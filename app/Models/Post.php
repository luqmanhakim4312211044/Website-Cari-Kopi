<?php

namespace App\Models;

use App\Models\Concerns\HasImage;
use App\Models\Concerns\HasReviews;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Satu post = satu cafe/toko. */
class Post extends Model
{
    use HasFactory, HasImage, HasReviews;

    protected $fillable = [
        'user_id', 'category_id', 'title', 'body', 'gambar',
        'latitude', 'longitude', 'provinsi', 'kota', 'alamat',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
