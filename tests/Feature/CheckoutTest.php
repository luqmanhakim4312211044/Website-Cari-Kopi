<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesShopData;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use CreatesShopData, RefreshDatabase;

    private function checkoutData(array $override = []): array
    {
        return array_merge(['nomor' => '0812-3456-7890', 'alamat' => 'Jl. Contoh No. 1, Batam'], $override);
    }

    public function test_checkout_page_redirects_to_cart_when_it_is_empty(): void
    {
        $this->actingAs($this->makeUser())
            ->get('/orders/create')
            ->assertRedirect(route('cart'))
            ->assertSessionHas('error');
    }

    public function test_checkout_page_opens_when_cart_has_items(): void
    {
        $buyer = $this->makeUser();
        $seller = $this->makeUser(User::ROLE_OWNER);
        $this->makeCart($buyer, $this->makeProduct($this->makeShop($seller)));

        $this->actingAs($buyer)->get('/orders/create')->assertOk();
    }

    public function test_totals_are_computed_on_the_server_and_every_item_is_saved(): void
    {
        $buyer = $this->makeUser(User::ROLE_CUSTOMER, 'silver'); // diskon 10%
        $seller = $this->makeUser(User::ROLE_OWNER);
        $product = $this->makeProduct($this->makeShop($seller), 20000, ['title' => 'Latte Spesial']);
        $this->makeCart($buyer, $product, 2);

        // Nilai palsu dari form (total, diskon, status, penjual) harus diabaikan.
        $response = $this->actingAs($buyer)->post('/orders', $this->checkoutData([
            'total' => 1, 'subtotal' => 1, 'diskon' => 99999, 'status' => 'paid', 'seller_id' => 999, 'user_id' => 999,
        ]));

        $order = Order::firstOrFail();
        $response->assertRedirect(route('orders.show', $order))->assertSessionHas('success');

        $this->assertEquals($buyer->id, $order->user_id);
        $this->assertEquals($seller->id, $order->seller_id);
        $this->assertEquals(40000, $order->subtotal);
        $this->assertEquals(4000, $order->diskon);
        $this->assertEquals(36000, $order->total);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame('081234567890', preg_replace('/\D/', '', $order->nomor));

        $this->assertSame(1, OrderItem::count());
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'nama' => 'Latte Spesial',
            'harga' => 20000,
            'jumlah' => 2,
            'total' => 40000,
        ]);

        $this->assertSame(0, Cart::where('user_id', $buyer->id)->count(), 'Keranjang harus kosong setelah checkout.');
    }

    public function test_items_from_different_sellers_are_split_into_separate_orders(): void
    {
        $buyer = $this->makeUser(User::ROLE_CUSTOMER, 'silver');
        $sellerA = $this->makeUser(User::ROLE_OWNER);
        $sellerB = $this->makeUser(User::ROLE_OWNER);
        $this->makeCart($buyer, $this->makeProduct($this->makeShop($sellerA), 20000));
        $this->makeCart($buyer, $this->makeProduct($this->makeShop($sellerB), 30000));

        $this->actingAs($buyer)
            ->post('/orders', $this->checkoutData())
            ->assertRedirect(route('orders.index'))
            ->assertSessionHas('success');

        $this->assertSame(2, Order::count());

        $orderA = Order::where('seller_id', $sellerA->id)->firstOrFail();
        $orderB = Order::where('seller_id', $sellerB->id)->firstOrFail();

        $this->assertEquals(18000, $orderA->total);
        $this->assertEquals(27000, $orderB->total);
        $this->assertSame(0, Cart::count());
    }

    public function test_checkout_with_an_empty_cart_is_rejected(): void
    {
        $this->actingAs($this->makeUser())
            ->post('/orders', $this->checkoutData())
            ->assertRedirect(route('cart'))
            ->assertSessionHas('error');

        $this->assertSame(0, Order::count());
    }

    public function test_phone_and_address_are_validated(): void
    {
        $buyer = $this->makeUser();
        $this->makeCart($buyer, $this->makeProduct($this->makeShop($this->makeUser(User::ROLE_OWNER))));

        $this->actingAs($buyer)->post('/orders', $this->checkoutData(['nomor' => 'abc']))->assertSessionHasErrors('nomor');
        $this->actingAs($buyer)->post('/orders', $this->checkoutData(['nomor' => '0812abc']))->assertSessionHasErrors('nomor');
        $this->actingAs($buyer)->post('/orders', $this->checkoutData(['nomor' => str_repeat('1', 21)]))->assertSessionHasErrors('nomor');
        $this->actingAs($buyer)->post('/orders', $this->checkoutData(['nomor' => '']))->assertSessionHasErrors('nomor');
        $this->actingAs($buyer)->post('/orders', $this->checkoutData(['alamat' => '']))->assertSessionHasErrors('alamat');
        $this->actingAs($buyer)->post('/orders', $this->checkoutData(['alamat' => str_repeat('a', 256)]))->assertSessionHasErrors('alamat');

        $this->assertSame(0, Order::count());
        $this->assertSame(1, Cart::count(), 'Keranjang harus tetap utuh kalau checkout ditolak.');
    }

    public function test_international_phone_format_is_accepted(): void
    {
        $buyer = $this->makeUser();
        $this->makeCart($buyer, $this->makeProduct($this->makeShop($this->makeUser(User::ROLE_OWNER))));

        $this->actingAs($buyer)
            ->post('/orders', $this->checkoutData(['nomor' => '+62 812 3456 7890']))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Order::count());
    }

    public function test_payment_proof_image_is_stored(): void
    {
        Storage::fake('public');

        $buyer = $this->makeUser();
        $this->makeCart($buyer, $this->makeProduct($this->makeShop($this->makeUser(User::ROLE_OWNER))));

        $this->actingAs($buyer)
            ->post('/orders', $this->checkoutData(['gambar' => UploadedFile::fake()->image('bukti.jpg')]))
            ->assertSessionHasNoErrors();

        $order = Order::firstOrFail();
        $this->assertNotNull($order->gambar);
        Storage::disk('public')->assertExists($order->gambar);
    }

    public function test_payment_proof_must_be_a_small_image(): void
    {
        Storage::fake('public');

        $buyer = $this->makeUser();
        $this->makeCart($buyer, $this->makeProduct($this->makeShop($this->makeUser(User::ROLE_OWNER))));

        $bad = [
            UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
            UploadedFile::fake()->create('shell.php', 10, 'application/x-php'),
            UploadedFile::fake()->image('besar.jpg')->size(3000),
        ];

        foreach ($bad as $file) {
            $this->actingAs($buyer)
                ->post('/orders', $this->checkoutData(['gambar' => $file]))
                ->assertSessionHasErrors('gambar');
        }

        $this->assertSame(0, Order::count());
    }

    public function test_checkout_does_not_touch_other_users_carts(): void
    {
        $buyer = $this->makeUser();
        $other = $this->makeUser();
        $product = $this->makeProduct($this->makeShop($this->makeUser(User::ROLE_OWNER)));
        $this->makeCart($buyer, $product);
        $othersCart = $this->makeCart($other, $product, 3);

        $this->actingAs($buyer)->post('/orders', $this->checkoutData());

        $this->assertDatabaseHas('carts', ['id' => $othersCart->id, 'quantity' => 3]);
    }
}
