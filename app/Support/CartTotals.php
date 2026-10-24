<?php

namespace App\Support;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Collection;

/** Menghitung subtotal, diskon member, dan total dari isi keranjang (selalu di server). */
final class CartTotals
{
    /**
     * @param  Collection<int, Cart>  $items  harus sudah di-load dengan relasi `product`
     * @return array{subtotal:int, rate:float, discount:int, total:int}
     */
    public static function make(Collection $items, User $user): array
    {
        $subtotal = (int) $items->sum(fn (Cart $item) => $item->subtotal);
        $rate = $user->discountRate();
        $discount = (int) round($subtotal * $rate);

        return [
            'subtotal' => $subtotal,
            'rate' => $rate,
            'discount' => $discount,
            'total' => $subtotal - $discount,
        ];
    }

    /**
     * Kelompokkan per penjual: setiap penjual menjadi satu pesanan terpisah saat checkout.
     * Relasi `product.user` harus sudah di-load.
     *
     * @return array{groups: Collection, totals: array{subtotal:int, rate:float, discount:int, total:int}}
     */
    public static function grouped(Collection $items, User $user): array
    {
        $groups = $items
            ->groupBy(fn (Cart $item) => $item->product->user_id)
            ->map(fn (Collection $group) => [
                'seller' => $group->first()->product->user,
                'items' => $group,
                'totals' => self::make($group, $user),
            ]);

        return [
            'groups' => $groups,
            'totals' => [
                'subtotal' => (int) $groups->sum(fn ($g) => $g['totals']['subtotal']),
                'rate' => $user->discountRate(),
                'discount' => (int) $groups->sum(fn ($g) => $g['totals']['discount']),
                'total' => (int) $groups->sum(fn ($g) => $g['totals']['total']),
            ],
        ];
    }
}
