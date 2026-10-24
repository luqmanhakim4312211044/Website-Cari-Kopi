<?php

namespace App\Models;

use App\Models\Concerns\HasImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Baris pesanan: salinan nama/harga produk saat checkout. */
class OrderItem extends Model
{
    use HasFactory, HasImage;

    protected $fillable = ['order_id', 'product_id', 'nama', 'harga', 'jumlah', 'total', 'gambar'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
