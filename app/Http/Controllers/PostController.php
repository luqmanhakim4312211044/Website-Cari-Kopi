<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersLists;
use App\Http\Controllers\Concerns\HandlesImages;
use App\Http\Requests\PostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

/** Manajemen cafe. Admin web melihat semua cafe; admin toko hanya cafe miliknya. */
class PostController extends Controller
{
    use FiltersLists, HandlesImages;

    public function index(Request $request)
    {
        $user = $request->user();
        [$search, $sort] = $this->listFilters($request);

        $query = Post::query()
            ->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->with(['category', 'user'])
            ->withCount('products')
            ->when($search !== '', function ($query) use ($search) {
                $like = $this->like($search);

                $query->where(function ($query) use ($like) {
                    $query->where('title', 'like', $like)
                        ->orWhere('kota', 'like', $like)
                        ->orWhere('alamat', 'like', $like);
                });
            });
        $this->applySort($query, $sort);

        return view('posts.index', [
            'posts' => $query->paginate(10)->withQueryString(),
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    public function create()
    {
        return view('posts.create', [
            'post' => new Post(),
            'categories' => Category::orderBy('title')->get(),
            'owners' => $this->owners(),
        ]);
    }

    public function store(PostRequest $request)
    {
        $data = $request->validated();
        $data['gambar'] = $this->storeImage($request->file('gambar'));
        // Admin web memilih pemilik lewat form; admin toko otomatis menjadi pemilik.
        $data['user_id'] ??= $request->user()->id;

        Post::create($data);

        return redirect()->route('posts.index')->with('success', 'Cafe berhasil ditambahkan.');
    }

    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        return view('posts.edit', [
            'post' => $post,
            'categories' => Category::orderBy('title')->get(),
            'owners' => $this->owners($post->user_id),
        ]);
    }

    public function update(PostRequest $request, Post $post)
    {
        $this->authorize('update', $post);

        $data = $request->validated();
        $data['gambar'] = $this->storeImage($request->file('gambar'), $post->gambar);

        $post->update($data);

        // Pindah pemilik: produk cafe ini ikut pindah, supaya pesanan baru masuk ke pemilik yang baru.
        if ($post->wasChanged('user_id')) {
            $post->products()->update(['user_id' => $post->user_id]);
        }

        return redirect()->route('posts.index')->with('success', 'Cafe berhasil diperbarui.');
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        // Produk ikut terhapus oleh database; bersihkan file gambarnya juga.
        $post->products->each(fn ($product) => $this->deleteImage($product->gambar));
        $this->deleteImage($post->gambar);

        $post->delete();

        return redirect()->route('posts.index')->with('success', 'Cafe berhasil dihapus.');
    }

    /** Calon pemilik cafe: admin toko dan admin web (plus pemilik saat ini, bila perannya sudah berubah). */
    private function owners(?int $currentOwnerId = null)
    {
        return User::query()
            ->where(function ($query) use ($currentOwnerId) {
                $query->whereIn('role', [User::ROLE_OWNER, User::ROLE_ADMIN]);

                if ($currentOwnerId) {
                    $query->orWhere('id', $currentOwnerId);
                }
            })
            ->orderBy('name')
            ->get();
    }
}
