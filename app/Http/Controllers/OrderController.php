<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesImages;
use App\Models\Order;
use App\Support\CartTotals;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Pesanan dari sisi pembeli. Pesanan masuk untuk penjual ada di DashboardController. */
class OrderController extends Controller
{
    use HandlesImages;

    /** "Pesanan saya": hanya pesanan yang di-checkout oleh user ini. */
    public function index(Request $request)
    {
        $status = $request->query('status');

        $orders = $request->user()->orders()
            ->with('seller')
            ->when(array_key_exists((string) $status, Order::STATUSES), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('orders.index', ['orders' => $orders, 'status' => $status]);
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $items = $user->cartItems()->with('product.user')->get();

        if ($items->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Keranjang masih kosong.');
        }

        $cart = CartTotals::grouped($items, $user);

        return view('orders.create', [
            'user' => $user,
            'groups' => $cart['groups'],
            'totals' => $cart['totals'],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nomor' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'alamat' => ['required', 'string', 'max:255'],
            'gambar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'nomor.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, + dan -.',
        ]);

        $user = $request->user();
        $items = $user->cartItems()->with('product.user')->get();

        if ($items->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Keranjang masih kosong.');
        }

        // Total dihitung ulang di server; nilai dari form tidak dipercaya.
        $groups = CartTotals::grouped($items, $user)['groups'];
        $proof = $this->storeImage($request->file('gambar'));

        // Satu pesanan per penjual, supaya tiap pemilik cafe hanya mengurus pesanannya sendiri.
        $orders = DB::transaction(function () use ($data, $user, $groups, $proof) {
            $created = collect();

            foreach ($groups as $sellerId => $group) {
                $totals = $group['totals'];

                $order = $user->orders()->create([
                    'seller_id' => $sellerId,
                    'nama' => $user->name,
                    'email' => $user->email,
                    'nomor' => $data['nomor'],
                    'alamat' => $data['alamat'],
                    'subtotal' => $totals['subtotal'],
                    'diskon' => $totals['discount'],
                    'total' => $totals['total'],
                    'status' => Order::STATUS_PENDING,
                    'gambar' => $proof,
                ]);

                $order->items()->createMany(
                    $group['items']->map(fn ($item) => [
                        'product_id' => $item->product_id,
                        'nama' => $item->product->title,
                        'harga' => $item->product->price,
                        'jumlah' => $item->quantity,
                        'total' => $item->subtotal,
                        'gambar' => $item->product->gambar,
                    ])->all()
                );

                $created->push($order);
            }

            $user->cartItems()->delete();

            return $created;
        });

        if ($orders->count() === 1) {
            return redirect()->route('orders.show', $orders->first())->with('success', 'Pesanan berhasil dibuat.');
        }

        return redirect()->route('orders.index')
            ->with('success', "Pesanan berhasil dibuat ({$orders->count()} pesanan, dipisah per cafe).");
    }

    public function show(Request $request, Order $order)
    {
        $this->authorize('view', $order);

        // Kembali ke tempat asal: dari dashboard (?from=dashboard) atau, bila bukan pembelinya, ke pesanan masuk.
        $user = $request->user();
        $fromDashboard = ($request->query('from') === 'dashboard' && $user->canManageShop())
            || $user->id !== $order->user_id;

        return view('orders.show', [
            'order' => $order->load(['items', 'seller']),
            'backUrl' => $fromDashboard ? route('dashboard.orders') : route('orders.index'),
            'backLabel' => $fromDashboard ? 'Kembali ke pesanan masuk' : 'Kembali ke pesanan saya',
        ]);
    }
}
