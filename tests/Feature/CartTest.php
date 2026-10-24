<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesShopData;
use Tests\TestCase;

class CartTest extends TestCase
{
    use CreatesShopData, RefreshDatabase;

    private function product(int $price = 50000): Product
    {
        return $this->makeProduct($this->makeShop($this->makeUser(User::ROLE_OWNER)), $price);
    }

    public function test_user_can_add_a_product_to_the_cart(): void
    {
        $user = $this->makeUser();
        $product = $this->product();

        $this->actingAs($user)
            ->post("/cart/{$product->id}", ['quantity' => 2])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('carts', ['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2]);
    }

    public function test_quantity_defaults_to_one_when_not_sent(): void
    {
        $user = $this->makeUser();
        $product = $this->product();

        $this->actingAs($user)->post("/cart/{$product->id}");

        $this->assertDatabaseHas('carts', ['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);
    }

    public function test_adding_the_same_product_accumulates_and_caps_at_99(): void
    {
        $user = $this->makeUser();
        $product = $this->product();

        $this->actingAs($user)->post("/cart/{$product->id}", ['quantity' => 60]);
        $this->actingAs($user)->post("/cart/{$product->id}", ['quantity' => 60]);

        $this->assertSame(1, Cart::count());
        $this->assertSame(99, Cart::first()->quantity);
    }

    public function test_invalid_quantity_is_rejected(): void
    {
        $user = $this->makeUser();
        $product = $this->product();

        foreach ([0, -1, 100, 'abc', 1.5] as $quantity) {
            $this->actingAs($user)
                ->post("/cart/{$product->id}", ['quantity' => $quantity])
                ->assertSessionHasErrors('quantity');
        }

        $this->assertSame(0, Cart::count());
    }

    public function test_adding_an_unknown_product_returns_404(): void
    {
        $this->actingAs($this->makeUser())->post('/cart/999')->assertNotFound();
    }

    public function test_cart_page_opens_with_and_without_items(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user)->get('/cart')->assertOk();

        $product = $this->product();
        $this->makeCart($user, $product, 2);

        $this->actingAs($user)->get('/cart')->assertOk()->assertSee($product->title);
    }

    public function test_updating_the_quantity_saves_it(): void
    {
        $user = $this->makeUser();
        $cart = $this->makeCart($user, $this->product());

        $this->actingAs($user)->patch("/cart/{$cart->id}", ['quantity' => 5])->assertSessionHas('success');

        $this->assertSame(5, $cart->fresh()->quantity);
    }

    public function test_update_validates_quantity(): void
    {
        $user = $this->makeUser();
        $cart = $this->makeCart($user, $this->product(), 3);

        foreach ([0, 100, 'abc', null] as $quantity) {
            $this->actingAs($user)
                ->patch("/cart/{$cart->id}", ['quantity' => $quantity])
                ->assertSessionHasErrors('quantity');
        }

        $this->assertSame(3, $cart->fresh()->quantity);
    }

    public function test_update_returns_recomputed_prices_as_json_with_member_discount(): void
    {
        $user = $this->makeUser(User::ROLE_CUSTOMER, 'gold'); // diskon 20%
        $cart = $this->makeCart($user, $this->product(50000));

        $this->actingAs($user)
            ->patchJson("/cart/{$cart->id}", ['quantity' => 2])
            ->assertOk()
            ->assertJson([
                'line_subtotal' => 'Rp 100.000',
                'subtotal' => 'Rp 100.000',
                'discount' => "\u{2212} Rp 20.000",
                'total' => 'Rp 80.000',
                'count' => 2,
            ]);
    }

    public function test_user_cannot_change_or_delete_another_users_cart(): void
    {
        $owner = $this->makeUser();
        $intruder = $this->makeUser();
        $cart = $this->makeCart($owner, $this->product(), 2);

        $this->actingAs($intruder)->patch("/cart/{$cart->id}", ['quantity' => 9])->assertForbidden();
        $this->actingAs($intruder)->delete("/cart/{$cart->id}")->assertForbidden();

        $this->assertSame(2, $cart->fresh()->quantity);
    }

    public function test_user_can_remove_an_item_from_the_cart(): void
    {
        $user = $this->makeUser();
        $cart = $this->makeCart($user, $this->product());

        $this->actingAs($user)->delete("/cart/{$cart->id}")->assertSessionHas('success');

        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
    }
}
