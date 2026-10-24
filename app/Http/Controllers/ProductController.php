<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersLists;
use App\Http\Controllers\Concerns\HandlesImages;
use App\Http\Requests\ProductRequest;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use FiltersLists, HandlesImages;

    /** Halaman publik detail produk. */
    public function show(Product $product)
    {
        $product->load('post')->loadAvg('reviews', 'rating')->loadCount('reviews');

        return view('products.show', [
            'product' => $product,
            'post' => $product->post,
            'reviews' => $product->reviews()->with('user')->latest()->get(),
        ]);
    }

    /* ---------- Dashboard ---------- */

    public function index(Request $request)
    {
        $user = $request->user();
        [$search, $sort] = $this->listFilters($request);

        $query = Product::query()
            ->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->with('post.user')
            ->when($search !== '', function ($query) use ($search) {
                $like = $this->like($search);

                $query->where(function ($query) use ($like) {
                    $query->where('title', 'like', $like)->orWhere('body', 'like', $like);
                });
            });
        $this->applySort($query, $sort);

        return view('products.index', [
            'products' => $query->paginate(10)->withQueryString(),
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    public function create(Request $request)
    {
        return view('products.create', [
            'product' => new Product(),
            'posts' => $this->availablePosts($request),
        ]);
    }

    public function store(ProductRequest $request)
    {
        $data = $request->validated();
        $data['gambar'] = $this->storeImage($request->file('gambar'));
        // Penjual selalu pemilik cafe, siapa pun yang menambahkan produknya.
        $data['user_id'] = Post::findOrFail($data['post_id'])->user_id;

        Product::create($data);

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        return view('products.edit', [
            'product' => $product,
            'posts' => $this->availablePosts($request),
        ]);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        $data = $request->validated();
        $data['gambar'] = $this->storeImage($request->file('gambar'), $product->gambar);
        $data['user_id'] = Post::findOrFail($data['post_id'])->user_id;

        $product->update($data);

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $this->deleteImage($product->gambar);
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }

    /** Cafe yang boleh dipilih: semua untuk admin web, miliknya sendiri untuk admin toko. */
    private function availablePosts(Request $request)
    {
        $user = $request->user();

        return Post::query()
            ->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->with('user')
            ->orderBy('title')
            ->get();
    }
}
