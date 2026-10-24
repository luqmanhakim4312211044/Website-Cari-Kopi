<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersLists;
use App\Http\Controllers\Concerns\HandlesImages;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Manajemen pengguna: peran & level member (khusus admin web). */
class UserController extends Controller
{
    use FiltersLists, HandlesImages;

    public function index(Request $request)
    {
        [$search, $sort] = $this->listFilters($request);

        $query = User::withCount('orders')
            ->when($search !== '', function ($query) use ($search) {
                $like = $this->like($search);

                $query->where(function ($query) use ($like) {
                    $query->where('name', 'like', $like)->orWhere('email', 'like', $like);
                });
            });
        $this->applySort($query, $sort);

        return view('users.index', [
            'users' => $query->paginate(15)->withQueryString(),
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'member' => ['required', Rule::in(array_keys(User::MEMBER_DISCOUNTS))],
        ]);

        if ($user->is($request->user()) && $data['role'] !== $user->role) {
            return back()->with('error', 'Kamu tidak bisa mengubah peran akunmu sendiri.');
        }

        // role & member tidak fillable, jadi diisi eksplisit.
        $user->role = $data['role'];
        $user->member = $data['member'];
        $user->save();

        return redirect()->route('users.index')->with('success', 'Data pengguna diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Kamu tidak bisa menghapus akunmu sendiri.');
        }

        // Cafe & produk milik pengguna ikut terhapus oleh database; bersihkan file gambarnya.
        $user->posts()->with('products')->get()->each(function ($post) {
            $post->products->each(fn ($product) => $this->deleteImage($product->gambar));
            $this->deleteImage($post->gambar);
        });
        $user->products->each(fn ($product) => $this->deleteImage($product->gambar));

        $user->delete();

        return back()->with('success', 'Pengguna berhasil dihapus.');
    }
}
