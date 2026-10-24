<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesShopData;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use CreatesShopData, RefreshDatabase;

    public function test_only_the_web_admin_can_open_user_and_category_management(): void
    {
        foreach ([User::ROLE_CUSTOMER, User::ROLE_OWNER] as $role) {
            $user = $this->makeUser($role);

            foreach (['/users', '/categories', '/categories/create'] as $url) {
                $this->actingAs($user)->get($url)->assertForbidden();
            }
        }

        $admin = $this->makeUser(User::ROLE_ADMIN);
        $this->actingAs($admin)->get('/users')->assertOk();
        $this->actingAs($admin)->get('/categories')->assertOk();
        $this->actingAs($admin)->get('/categories/create')->assertOk();
    }

    public function test_shop_owner_cannot_change_roles_or_members(): void
    {
        $owner = $this->makeUser(User::ROLE_OWNER);
        $target = $this->makeUser();

        $this->actingAs($owner)->patch("/users/{$target->id}", ['role' => 'admin', 'member' => 'diamond'])->assertForbidden();
        $this->actingAs($owner)->delete("/users/{$target->id}")->assertForbidden();

        $this->assertSame('customer', $target->fresh()->role);
        $this->assertSame('none', $target->fresh()->member);
    }

    public function test_admin_can_change_role_and_member(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $target = $this->makeUser();

        $this->actingAs($admin)
            ->patch("/users/{$target->id}", ['role' => 'owner', 'member' => 'gold'])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertSame('owner', $target->fresh()->role);
        $this->assertSame('gold', $target->fresh()->member);
    }

    public function test_role_and_member_must_be_valid_values(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $target = $this->makeUser();

        $this->actingAs($admin)->patch("/users/{$target->id}", ['role' => 'superadmin', 'member' => 'gold'])->assertSessionHasErrors('role');
        $this->actingAs($admin)->patch("/users/{$target->id}", ['role' => 'owner', 'member' => 'platinum'])->assertSessionHasErrors('member');
        $this->actingAs($admin)->patch("/users/{$target->id}", ['member' => 'gold'])->assertSessionHasErrors('role');
        $this->actingAs($admin)->patch("/users/{$target->id}", ['role' => 'owner'])->assertSessionHasErrors('member');

        $this->assertSame('customer', $target->fresh()->role);
        $this->assertSame('none', $target->fresh()->member);
    }

    public function test_admin_cannot_change_own_role_but_can_change_own_member(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $this->actingAs($admin)
            ->patch("/users/{$admin->id}", ['role' => 'customer', 'member' => 'none'])
            ->assertSessionHas('error');
        $this->assertSame('admin', $admin->fresh()->role);

        $this->actingAs($admin)
            ->patch("/users/{$admin->id}", ['role' => 'admin', 'member' => 'gold'])
            ->assertRedirect(route('users.index'));
        $this->assertSame('gold', $admin->fresh()->member);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $this->actingAs($admin)->delete("/users/{$admin->id}")->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_deleting_a_user_removes_their_cafes_products_and_image_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('gambarku/cafe.jpg', 'x');
        Storage::disk('public')->put('gambarku/produk.jpg', 'x');

        $admin = $this->makeUser(User::ROLE_ADMIN);
        $owner = $this->makeUser(User::ROLE_OWNER);
        $post = $this->makeShop($owner, null, ['gambar' => 'gambarku/cafe.jpg']);
        $this->makeProduct($post, 10000, ['gambar' => 'gambarku/produk.jpg']);

        $this->actingAs($admin)->delete("/users/{$owner->id}")->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $owner->id]);
        $this->assertSame(0, Post::count());
        $this->assertSame(0, Product::count());
        Storage::disk('public')->assertMissing('gambarku/cafe.jpg');
        Storage::disk('public')->assertMissing('gambarku/produk.jpg');
    }

    public function test_user_list_search_does_not_crash_on_special_characters(): void
    {
        $this->actingAs($this->makeUser(User::ROLE_ADMIN))
            ->get('/users?search='.urlencode("%_\\' OR 1=1 --"))
            ->assertOk();
    }

    /* ---------- Kategori ---------- */

    public function test_admin_can_create_a_category_but_not_a_duplicate(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $this->actingAs($admin)->post('/categories', ['title' => 'Kopi'])->assertRedirect(route('categories.index'));
        $this->assertSame(1, Category::count());

        $this->actingAs($admin)->post('/categories', ['title' => 'Kopi'])->assertSessionHasErrors('title');
        $this->assertSame(1, Category::count());
    }

    public function test_category_title_is_validated(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $this->actingAs($admin)->post('/categories', ['title' => ''])->assertSessionHasErrors('title');
        $this->actingAs($admin)->post('/categories', ['title' => str_repeat('a', 51)])->assertSessionHasErrors('title');

        $this->assertSame(0, Category::count());
    }

    public function test_category_can_keep_its_own_title_when_edited(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $category = $this->makeCategory('Kopi');

        $this->actingAs($admin)->patch("/categories/{$category->id}", ['title' => 'Kopi'])->assertSessionHasNoErrors();
        $this->actingAs($admin)->patch("/categories/{$category->id}", ['title' => 'Kopi Spesial'])->assertRedirect(route('categories.index'));

        $this->assertSame('Kopi Spesial', $category->fresh()->title);
    }

    public function test_category_in_use_cannot_be_deleted_but_an_unused_one_can(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $used = $this->makeCategory('Dipakai');
        $unused = $this->makeCategory('Kosong');
        $this->makeShop($this->makeUser(User::ROLE_OWNER), $used);

        $this->actingAs($admin)->delete("/categories/{$used->id}")->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $used->id]);

        $this->actingAs($admin)->delete("/categories/{$unused->id}")->assertRedirect(route('categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $unused->id]);
    }
}
