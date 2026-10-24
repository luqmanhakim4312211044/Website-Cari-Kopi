<?php

namespace Tests\Unit;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Support\CartTotals;
use Tests\TestCase;

class CartTotalsTest extends TestCase
{
    private function userWithMember(string $member): User
    {
        return (new User())->forceFill(['member' => $member]);
    }

    private function cartItem(int $price, int $quantity, int $sellerId = 1): Cart
    {
        $seller = (new User())->forceFill(['id' => $sellerId, 'name' => "Penjual {$sellerId}"]);

        $product = (new Product(['price' => $price]))->forceFill(['user_id' => $sellerId]);
        $product->setRelation('user', $seller);

        $cart = new Cart(['quantity' => $quantity]);
        $cart->setRelation('product', $product);

        return $cart;
    }

    public function test_totals_without_member_have_no_discount(): void
    {
        $totals = CartTotals::make(collect([$this->cartItem(50000, 2)]), $this->userWithMember('none'));

        $this->assertSame(100000, $totals['subtotal']);
        $this->assertSame(0, $totals['discount']);
        $this->assertSame(100000, $totals['total']);
    }

    public function test_each_member_level_gets_its_discount(): void
    {
        $items = collect([$this->cartItem(50000, 2)]); // subtotal 100.000

        $expected = ['silver' => 10000, 'gold' => 20000, 'diamond' => 30000];

        foreach ($expected as $member => $discount) {
            $totals = CartTotals::make($items, $this->userWithMember($member));

            $this->assertSame($discount, $totals['discount'], "Diskon untuk member {$member} salah.");
            $this->assertSame(100000 - $discount, $totals['total']);
        }
    }

    public function test_discount_is_rounded_to_whole_rupiah(): void
    {
        // 30% dari 33.333 = 9.999,9 → dibulatkan menjadi 10.000.
        $totals = CartTotals::make(collect([$this->cartItem(33333, 1)]), $this->userWithMember('diamond'));

        $this->assertSame(10000, $totals['discount']);
        $this->assertSame(23333, $totals['total']);
    }

    public function test_unknown_member_level_gets_no_discount(): void
    {
        $totals = CartTotals::make(collect([$this->cartItem(20000, 1)]), $this->userWithMember('platinum'));

        $this->assertSame(0, $totals['discount']);
        $this->assertSame(20000, $totals['total']);
    }

    public function test_empty_cart_totals_are_zero(): void
    {
        $totals = CartTotals::make(collect(), $this->userWithMember('gold'));

        $this->assertSame(0, $totals['subtotal']);
        $this->assertSame(0, $totals['total']);
    }

    public function test_items_are_grouped_per_seller(): void
    {
        $items = collect([
            $this->cartItem(20000, 1, sellerId: 1),
            $this->cartItem(10000, 3, sellerId: 1),
            $this->cartItem(30000, 1, sellerId: 2),
        ]);

        $result = CartTotals::grouped($items, $this->userWithMember('silver'));

        $this->assertCount(2, $result['groups']);
        $this->assertSame(50000, $result['groups'][1]['totals']['subtotal']);
        $this->assertSame(30000, $result['groups'][2]['totals']['subtotal']);

        // Total keseluruhan = jumlah dari semua kelompok.
        $this->assertSame(80000, $result['totals']['subtotal']);
        $this->assertSame(8000, $result['totals']['discount']);
        $this->assertSame(72000, $result['totals']['total']);
    }
}
