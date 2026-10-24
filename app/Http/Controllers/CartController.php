<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Support\CartTotals;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $items = $user->cartItems()->with(['product.post', 'product.user'])->get();
        $cart = CartTotals::grouped($items, $user);

        return view('cart', [
            'items' => $items,
            'groups' => $cart['groups'],
            'totals' => $cart['totals'],
            'member' => $user->member,
        ]);
    }

    public function add(Request $request, Product $product)
    {
        $data = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);
        $quantity = $data['quantity'] ?? 1;

        $item = $request->user()->cartItems()->firstOrNew(['product_id' => $product->id]);
        $item->quantity = min(99, ($item->quantity ?? 0) + $quantity);
        $item->save();

        return redirect(url()->previous().'#produk')
            ->with('success', "{$quantity} × {$product->title} ditambahkan ke keranjang.");
    }

    public function update(Request $request, Cart $cart)
    {
        $this->authorize('update', $cart);

        $cart->update($request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]));

        // Dipanggil lewat fetch dari halaman keranjang: balas angka terbaru agar harga langsung berubah.
        if ($request->expectsJson()) {
            $user = $request->user();
            $items = $user->cartItems()->with(['product.user'])->get();
            $totals = CartTotals::grouped($items, $user)['totals'];

            return response()->json([
                'line_subtotal' => $this->rupiah($cart->fresh('product')->subtotal),
                'subtotal' => $this->rupiah($totals['subtotal']),
                'discount' => '− '.$this->rupiah($totals['discount']),
                'total' => $this->rupiah($totals['total']),
                'count' => (int) $items->sum('quantity'),
            ]);
        }

        return back()->with('success', 'Jumlah produk diperbarui.');
    }

    public function destroy(Cart $cart)
    {
        $this->authorize('delete', $cart);

        $cart->delete();

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }

    private function rupiah(int $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }
}
