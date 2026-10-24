<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';       // admin web: mengontrol seluruh data
    public const ROLE_OWNER = 'owner';       // admin toko: hanya cafe & produk miliknya
    public const ROLE_CUSTOMER = 'customer';

    public const ROLES = [
        self::ROLE_CUSTOMER => 'Pelanggan',
        self::ROLE_OWNER => 'Admin Toko',
        self::ROLE_ADMIN => 'Admin Web',
    ];

    /** Diskon per level member (0.1 = 10%). */
    public const MEMBER_DISCOUNTS = [
        'none' => 0,
        'silver' => 0.1,
        'gold' => 0.2,
        'diamond' => 0.3,
    ];

    // `role` dan `member` sengaja tidak fillable: hanya diubah lewat kode/admin.
    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['email_verified_at' => 'datetime'];

    /** Admin web (akses penuh). */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /** Admin web atau admin toko: boleh masuk dashboard dan mengelola toko. */
    public function canManageShop(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_OWNER], true);
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    public function discountRate(): float
    {
        return self::MEMBER_DISCOUNTS[$this->member] ?? 0;
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    /** Pesanan yang dibuat user ini sebagai pembeli. */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** Pesanan masuk untuk cafe milik user ini (sebagai penjual). */
    public function sales(): HasMany
    {
        return $this->hasMany(Order::class, 'seller_id');
    }
}
