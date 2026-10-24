<?php

namespace App\Models\Concerns;

use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasReviews
{
    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    /**
     * Urutkan berdasarkan rating: 'rating_desc' (tertinggi) atau 'rating_asc' (terendah).
     * Yang belum punya ulasan selalu di paling bawah.
     * Query harus sudah memanggil ->withAvg('reviews', 'rating').
     */
    public function scopeSortByRating(Builder $query, ?string $sort): Builder
    {
        if (! in_array($sort, ['rating_desc', 'rating_asc'], true)) {
            return $query;
        }

        return $query
            ->orderByRaw('reviews_avg_rating is null')
            ->orderBy('reviews_avg_rating', $sort === 'rating_asc' ? 'asc' : 'desc');
    }
}
