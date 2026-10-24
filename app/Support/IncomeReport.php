<?php

namespace App\Support;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Data laporan penghasilan.
 * Admin toko: hanya pesanan masuk untuk cafe miliknya. Admin web: seluruh situs, dirinci per toko.
 * Pendapatan = total pesanan berstatus "sudah bayar" (setelah diskon member).
 */
final class IncomeReport
{
    public static function make(User $user, ?Carbon $from, ?Carbon $to): array
    {
        $admin = $user->isAdmin();

        $orders = fn () => Order::query()
            ->when(! $admin, fn ($query) => $query->where('seller_id', $user->id))
            ->when($from, fn ($query) => $query->where('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->where('created_at', '<=', $to));

        $paid = Order::STATUS_PAID;
        $pending = Order::STATUS_PENDING;

        $totals = [
            'orders' => $orders()->count(),
            'paid_orders' => $orders()->where('status', $paid)->count(),
            'pending_orders' => $orders()->where('status', $pending)->count(),
            'revenue' => (int) $orders()->where('status', $paid)->sum('total'),
            'pending_amount' => (int) $orders()->where('status', $pending)->sum('total'),
            'discount' => (int) $orders()->where('status', $paid)->sum('diskon'),
        ];

        // Produk terlaris (hanya pesanan yang sudah dibayar). Nilai per produk sebelum diskon member.
        $products = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', $paid)
            ->when(! $admin, fn ($query) => $query->where('orders.seller_id', $user->id))
            ->when($from, fn ($query) => $query->where('orders.created_at', '>=', $from))
            ->when($to, fn ($query) => $query->where('orders.created_at', '<=', $to))
            ->groupBy('order_items.nama')
            ->selectRaw('order_items.nama as nama, sum(order_items.jumlah) as qty, sum(order_items.total) as revenue')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        // Rincian per toko, hanya untuk admin web.
        $shops = $admin
            ? DB::table('orders')
                ->leftJoin('users', 'users.id', '=', 'orders.seller_id')
                ->when($from, fn ($query) => $query->where('orders.created_at', '>=', $from))
                ->when($to, fn ($query) => $query->where('orders.created_at', '<=', $to))
                ->groupBy('orders.seller_id', 'users.name')
                ->selectRaw("coalesce(users.name, '(tanpa penjual)') as seller, count(*) as orders, sum(case when orders.status = 'paid' then orders.total else 0 end) as revenue, sum(case when orders.status = 'pending' then orders.total else 0 end) as pending")
                ->orderByDesc('revenue')
                ->get()
            : collect();

        // Daftar pesanan, hanya untuk admin toko (admin web cukup rekap per toko).
        $list = $admin
            ? collect()
            : $orders()->with('items')->latest()->limit(200)->get();

        return [
            'scope' => $admin ? 'Seluruh situs' : 'Toko: '.$user->name,
            'admin' => $admin,
            'from' => $from,
            'to' => $to,
            'totals' => $totals,
            'products' => $products,
            'shops' => $shops,
            'orders' => $list,
        ];
    }
}
