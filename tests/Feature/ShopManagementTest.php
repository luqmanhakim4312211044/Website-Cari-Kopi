<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesShopData;
use Tests\TestCase;

class ShopManagementTest extends TestCase
{
    use CreatesShopData, RefreshDatabase;

    private function postPayload(Category $category, array $override = []): array
    {
        return array_merge([
            'title' => 'Kopi Senja',
            'body' => 'Tempat nongkrong santai di pinggir pantai.',
            'category_id' => $category->id,
            'latitude' => '1.1234567',
            'longitude' => '104.0305000',
            'provinsi' => 'Kepulauan Riau',
            'kota' => 'Batam',
            'alamat' => 'Jl. Contoh No. 1',
        ], $override);
    }

    private function productPayload(int $postId, array $override = []): array
    {
        return array_merge([
            'post_id' => $postId,
            'title' => 'Latte Spesial',
            'body' => 'Kopi susu dengan latte art.',
            'price' => 25000,
        ], $override);
    }

    /* ---------- Cafe ---------- */

    public function test_owner_can_create_a_cafe_and_always_becomes_its_owner(): void
    {
        Storage::fake('public');
        $owner = $this->makeUser(User::ROLE_OWNER);
        $other = $this->makeUser(User::ROLE_OWNER);
        $category = $this->makeCategory();

        // user_id dari form harus diabaikan untuk admin toko.
        $this->actingAs($owner)
            ->post('/posts', $this->postPayload($category, [
                'user_id' => $other->id,
                'gambar' => UploadedFile::fake()->image('toko.jpg'),
            ]))
            ->assertRedirect(route('posts.index'))
            ->assertSessionHas('success');

        $post = Post::firstOrFail();
        $this->assertEquals($owner->id, $post->user_id);
        Storage::disk('public')->assertExists($post->gambar);
    }

    public function test_negative_coordinates_are_accepted(): void
    {
        $owner = $this->makeUser(User::ROLE_OWNER);

        $this->actingAs($owner)
            ->post('/posts', $this->postPayload($this->makeCategory(), ['latitude' => '-6.2', 'longitude' => '106.8']))
            ->assertSessionHasNoErrors();

        $this->assertEqualsWithDelta(-6.2, Post::firstOrFail()->latitude, 0.001);
    }

    public function test_cafe_input_is_validated(): void
    {
        $owner = $this->makeUser(User::ROLE_OWNER);
        $category = $this->makeCategory();

        $cases = [
            [['title' => ''], 'title'],
            [['title' => str_repeat('a', 61)], 'title'],
            [['body' => str_repeat('a', 1001)], 'body'],
            [['category_id' => 9999], 'category_id'],
            [['latitude' => 91], 'latitude'],
            [['latitude' => 'abc'], 'latitude'],
            [['longitude' => 181], 'longitude'],
            [['kota' => ''], 'kota'],
            [['alamat' => str_repeat('a', 256)], 'alamat'],
        ];

        foreach ($cases as [$override, $field]) {
            $this->actingAs($owner)
                ->post('/posts', $this->postPayload($category, $override))
                ->assertSessionHasErrors($field);
        }

        $this->assertSame(0, Post::count());
    }

    public function test_cafe_image_must_be_a_small_image(): void
    {
        Storage::fake('public');
        $owner = $this->makeUser(User::ROLE_OWNER);
        $category = $this->makeCategory();

        foreach ([
            UploadedFile::fake()->create('shell.php', 10, 'application/x-php'),
            UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
            UploadedFile::fake()->image('besar.jpg')->size(3000),
        ] as $file) {
            $this->actingAs($owner)
                ->post('/posts', $this->postPayload($category, ['gambar' => $file]))
                ->assertSessionHasErrors('gambar');
        }

        $this->assertSame(0, Post::count());
    }

    public function test_admin_must_choose_the_owner_of_a_new_cafe(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $owner = $this->makeUser(User::ROLE_OWNER);
        $category = $this->makeCategory();

        $this->actingAs($admin)->post('/posts', $this->postPayload($category))->assertSessionHasErrors('user_id');
        $this->actingAs($admin)->post('/posts', $this->postPayload($category, ['user_id' => 9999]))->assertSessionHasErrors('user_id');
        $this->assertSame(0, Post::count());

        $this->actingAs($admin)
            ->post('/posts', $this->postPayload($category, ['user_id' => $owner->id]))
            ->assertRedirect(route('posts.index'));

        $this->assertEquals($owner->id, Post::firstOrFail()->user_id);
    }

    public function test_owner_only_sees_own_cafes_in_the_list(): void
    {
        $owner = $this->makeUser(User::ROLE_OWNER);
        $mine = $this->makeShop($owner);
        $this->makeShop($this->makeUser(User::ROLE_OWNER));

        $this->actingAs($owner)->get('/posts')
            ->assertViewHas('posts', fn ($posts) => $posts->getCollection()->pluck('id')->all() === [$mine->id]);

        $this->actingAs($this->makeUser(User::ROLE_ADMIN))->get('/posts')
            ->assertViewHas('posts', fn ($posts) => $posts->total() === 2);
    }

    public function test_updating_a_cafe_with_a_new_image_replaces_the_old_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('gambarku/lama.jpg', 'isi lama');

        $owner = $this->makeUser(User::ROLE_OWNER);
        $category = $this->makeCategory();
        $post = $this->makeShop($owner, $category, ['gambar' => 'gambarku/lama.jpg']);

        $this->actingAs($owner)
            ->patch("/posts/{$post->id}", $this->postPayload($category, [
                'title' => 'Judul Baru',
                'gambar' => UploadedFile::fake()->image('baru.jpg'),
            ]))
            ->assertRedirect(route('posts.index'));

        $post->refresh();
        $this->assertSame('Judul Baru', $post->title);
        $this->assertNotSame('gambarku/lama.jpg', $post->gambar);
        Storage::disk('public')->assertMissing('gambarku/lama.jpg');
        Storage::disk('public')->assertExists($post->gambar);
    }

    public function test_updating_a_cafe_without_a_new_image_keeps_the_old_one(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('gambarku/lama.jpg', 'isi lama');

        $owner = $this->makeUser(User::ROLE_OWNER);
        $category = $this->makeCategory();
        $post = $this->makeShop($owner, $category, ['gambar' => 'gambarku/lama.jpg']);

        $this->actingAs($owner)->patch("/posts/{$post->id}", $this->postPayload($category, ['title' => 'Judul Baru']));

        $this->assertSame('gambarku/lama.jpg', $post->fresh()->gambar);
        Storage::disk('public')->assertExists('gambarku/lama.jpg');
    }

    public function test_owner_cannot_touch_another_owners_cafe(): void
    {
        $owner = $this->makeUser(User::ROLE_OWNER);
        $intruder = $this->makeUser(User::ROLE_OWNER);
        $category = $this->makeCategory();
        $post = $this->makeShop($owner, $category, ['title' => 'Asli']);

        $this->actingAs($intruder)->get("/posts/{$post->id}/edit")->assertForbidden();
        $this->actingAs($intruder)->patch("/posts/{$post->id}", $this->postPayload($category, ['title' => 'Dibajak']))->assertForbidden();
        $this->actingAs($intruder)->delete("/posts/{$post->id}")->assertForbidden();

        $this->assertSame('Asli', $post->fresh()->title);
    }

    public function test_customer_cannot_manage_cafes(): void
    {
        $customer = $this->makeUser();
        $category = $this->makeCategory();

        $this->actingAs($customer)->post('/posts', $this->postPayload($category))->assertForbidden();
        $this->assertSame(0, Post::count());
    }

    public function test_admin_can_move_a_cafe_to_another_owner_and_its_products_follow(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $oldOwner = $this->makeUser(User::ROLE_OWNER);
        $newOwner = $this->makeUser(User::ROLE_OWNER);
        $category = $this->makeCategory();
        $post = $this->makeShop($oldOwner, $category);
        $product = $this->makeProduct($post);

        $this->actingAs($admin)
            ->patch("/posts/{$post->id}", $this->postPayload($category, ['user_id' => $newOwner->id]))
            ->assertRedirect(route('posts.index'));

        $this->assertEquals($newOwner->id, $post->fresh()->user_id);
        $this->assertEquals($newOwner->id, $product->fresh()->user_id);
    }

    public function test_deleting_a_cafe_removes_its_products_and_image_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('gambarku/cafe.jpg', 'x');
        Storage::disk('public')->put('gambarku/produk.jpg', 'x');

        $owner = $this->makeUser(User::ROLE_OWNER);
        $post = $this->makeShop($owner, null, ['gambar' => 'gambarku/cafe.jpg']);
        $this->makeProduct($post, 10000, ['gambar' => 'gambarku/produk.jpg']);

        $this->actingAs($owner)->delete("/posts/{$post->id}")->assertRedirect(route('posts.index'));

        $this->assertSame(0, Post::count());
        $this->assertSame(0, Product::count());
        Storage::disk('public')->assertMissing('gambarku/cafe.jpg');
        Storage::disk('public')->assertMissing('gambarku/produk.jpg');
    }

    /* ---------- Produk ---------- */

    public function test_owner_can_add_a_product_to_own_cafe(): void
    {
        Storage::fake('public');
        $owner = $this->makeUser(User::ROLE_OWNER);
        $post = $this->makeShop($owner);

        $this->actingAs($owner)
            ->post('/products', $this->productPayload($post->id, ['gambar' => UploadedFile::fake()->image('latte.jpg')]))
            ->assertRedirect(route('products.index'))
            ->assertSessionHas('success');

        $product = Product::firstOrFail();
        $this->assertEquals($owner->id, $product->user_id);
        $this->assertEquals($post->id, $product->post_id);
        Storage::disk('public')->assertExists($product->gambar);
    }

    public function test_owner_cannot_add_a_product_to_someone_elses_cafe(): void
    {
        $owner = $this->makeUser(User::ROLE_OWNER);
        $otherShop = $this->makeShop($this->makeUser(User::ROLE_OWNER));

        $this->actingAs($owner)
            ->post('/products', $this->productPayload($otherShop->id))
            ->assertSessionHasErrors('post_id');

        $this->assertSame(0, Product::count());
    }

    public function test_admin_can_add_a_product_to_any_cafe_and_the_cafe_owner_becomes_the_seller(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $owner = $this->makeUser(User::ROLE_OWNER);
        $post = $this->makeShop($owner);

        $this->actingAs($admin)->post('/products', $this->productPayload($post->id))->assertSessionHasNoErrors();

        $this->assertEquals($owner->id, Product::firstOrFail()->user_id);
    }

    public function test_product_price_and_text_are_validated(): void
    {
        $owner = $this->makeUser(User::ROLE_OWNER);
        $post = $this->makeShop($owner);

        $cases = [
            [['price' => -1], 'price'],
            [['price' => 1.5], 'price'],
            [['price' => 'gratis'], 'price'],
            [['price' => 100000001], 'price'],
            [['price' => ''], 'price'],
            [['title' => ''], 'title'],
            [['title' => str_repeat('a', 101)], 'title'],
            [['body' => str_repeat('a', 1001)], 'body'],
            [['post_id' => 9999], 'post_id'],
        ];

        foreach ($cases as [$override, $field]) {
            $this->actingAs($owner)
                ->post('/products', $this->productPayload($post->id, $override))
                ->assertSessionHasErrors($field);
        }

        $this->assertSame(0, Product::count());
    }

    public function test_product_price_boundaries_are_accepted(): void
    {
        $owner = $this->makeUser(User::ROLE_OWNER);
        $post = $this->makeShop($owner);

        $this->actingAs($owner)->post('/products', $this->productPayload($post->id, ['price' => 0]))->assertSessionHasNoErrors();
        $this->actingAs($owner)->post('/products', $this->productPayload($post->id, ['price' => 100000000]))->assertSessionHasNoErrors();

        $this->assertSame(2, Product::count());
    }

    public function test_owner_can_update_own_product_but_not_someone_elses(): void
    {
        $owner = $this->makeUser(User::ROLE_OWNER);
        $intruder = $this->makeUser(User::ROLE_OWNER);
        $post = $this->makeShop($owner);
        $intruderPost = $this->makeShop($intruder);
        $product = $this->makeProduct($post, 10000, ['title' => 'Asli']);

        $this->actingAs($intruder)->get("/products/{$product->id}/edit")->assertForbidden();

        // Memakai cafe miliknya sendiri: validasi lolos, lalu ProductPolicy menolak (403).
        $this->actingAs($intruder)
            ->patch("/products/{$product->id}", $this->productPayload($intruderPost->id, ['title' => 'Dibajak']))
            ->assertForbidden();
        $this->actingAs($intruder)->delete("/products/{$product->id}")->assertForbidden();

        // Memakai cafe milik korban: validasi menolak lebih dulu karena cafe itu bukan miliknya.
        $this->actingAs($intruder)
            ->patch("/products/{$product->id}", $this->productPayload($post->id, ['title' => 'Dibajak']))
            ->assertSessionHasErrors('post_id');

        $this->assertSame('Asli', $product->fresh()->title);
        $this->assertSame(1, Product::count());

        $this->actingAs($owner)
            ->patch("/products/{$product->id}", $this->productPayload($post->id, ['title' => 'Diubah', 'price' => 30000]))
            ->assertRedirect(route('products.index'));

        $this->assertSame('Diubah', $product->fresh()->title);
        $this->assertEquals(30000, $product->fresh()->price);
    }

    public function test_deleting_a_product_removes_its_image_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('gambarku/produk.jpg', 'x');

        $owner = $this->makeUser(User::ROLE_OWNER);
        $product = $this->makeProduct($this->makeShop($owner), 10000, ['gambar' => 'gambarku/produk.jpg']);

        $this->actingAs($owner)->delete("/products/{$product->id}")->assertRedirect(route('products.index'));

        $this->assertSame(0, Product::count());
        Storage::disk('public')->assertMissing('gambarku/produk.jpg');
    }

    public function test_owner_only_sees_own_products_in_the_list(): void
    {
        $owner = $this->makeUser(User::ROLE_OWNER);
        $mine = $this->makeProduct($this->makeShop($owner));
        $this->makeProduct($this->makeShop($this->makeUser(User::ROLE_OWNER)));

        $this->actingAs($owner)->get('/products')
            ->assertViewHas('products', fn ($products) => $products->getCollection()->pluck('id')->all() === [$mine->id]);
    }

    public function test_list_search_does_not_crash_on_special_characters(): void
    {
        $owner = $this->makeUser(User::ROLE_OWNER);

        $this->actingAs($owner)->get('/posts?search='.urlencode("%_\\' OR 1=1 --"))->assertOk();
        $this->actingAs($owner)->get('/products?search='.urlencode("%_\\' OR 1=1 --"))->assertOk();
    }
}
