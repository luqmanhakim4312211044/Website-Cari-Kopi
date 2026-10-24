<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function storeForPost(Request $request, Post $post)
    {
        return $this->store($request, $post);
    }

    public function storeForProduct(Request $request, Product $product)
    {
        return $this->store($request, $product);
    }

    public function update(Request $request, Review $review)
    {
        $this->authorize('update', $review);

        $review->update($this->validated($request));

        return $this->backToReviews('Ulasan berhasil diperbarui.');
    }

    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);

        $review->delete();

        return $this->backToReviews('Ulasan berhasil dihapus.');
    }

    private function store(Request $request, Model $reviewable)
    {
        $user = $request->user();

        if ($reviewable->reviews()->where('user_id', $user->id)->exists()) {
            return redirect(url()->previous().'#ulasan')->with('error', 'Kamu sudah pernah memberi ulasan.');
        }

        $reviewable->reviews()->create($this->validated($request) + ['user_id' => $user->id]);

        return $this->backToReviews('Ulasan berhasil ditambahkan.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);
    }

    private function backToReviews(string $message)
    {
        return redirect(url()->previous().'#ulasan')->with('success', $message);
    }
}
