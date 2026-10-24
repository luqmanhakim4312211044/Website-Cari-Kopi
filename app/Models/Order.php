<?php

namespace App\Models;

use App\Models\Concerns\HasImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, HasImage;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';

    public const STATUSES = [
        self::STATUS_PENDING => 'Belum bayar',
        self::STATUS_PAID => 'Sudah bayar',
    ];

    protected $fillable = [
        'user_id', 'seller_id', 'nama', 'email', 'nomor', 'alamat',
        'subtotal', 'diskon', 'total', 'status', 'gambar',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
