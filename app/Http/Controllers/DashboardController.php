<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersLists;
use App\Models\Order;
use App\Models\Post;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Dashboard. Admin web melihat data seluruh situs; admin toko hanya data tokonya sendiri.
 */
class DashboardController extends Controller
{
    use FiltersLists;

    public function index(Request $request)
    {
        $user = $request->user();
        $orders = $this->scopedOrders($user);

        return view('dashboard.index', [
            'user' => $user,
            'stats' => [
                'cafes' => $user->isAdmin() ? Post::count() : $user->posts()->count(),
                'products' => $user->isAdmin() ? Product::count() : $user->products()->count(),
                'pending' => (clone $orders)->where('status', Order::STATUS_PENDING)->count(),
                'revenue' => (int) (clone $orders)->where('status', Order::STATUS_PAID)->sum('total'),
                'users' => $user->isAdmin() ? User::count() : null,
            ],
            'recentOrders' => (clone $orders)->with('items')->latest()->take(5)->get(),
        ]);
    }

    public function orders(Request $request)
    {
        $user = $request->user();
        [$search, $sort] = $this->listFilters($request);
        $status = $request->query('status');

        $query = $this->scopedOrders($user)
            ->with(['items', 'seller'])
            ->when(array_key_exists((string) $status, Order::STATUSES), fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $like = $this->like($search);

                $query->where(function ($query) use ($like, $search) {
                    $query->where('nama', 'like', $like)
                        ->orWhere('nomor', 'like', $like)
                        ->orWhere('id', ctype_digit($search) ? (int) $search : 0)
                        ->orWhereHas('items', fn ($items) => $items->where('nama', 'like', $like));
                });
            });
        $this->applySort($query, $sort);

        return view('dashboard.orders', [
            'orders' => $query->paginate(10)->withQueryString(),
            'status' => $status,
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    public function updateOrder(Request $request, Order $order)
    {
        $this->authorize('manage', $order);

        $order->update($request->validate([
            'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
        ]));

        return back()->with('success', "Status pesanan #{$order->id} diperbarui.");
    }

    /** Admin web: semua pesanan. Admin toko: pesanan untuk cafe miliknya. */
    private function scopedOrders(User $user)
    {
        return $user->isAdmin() ? Order::query() : $user->sales();
    }
}
