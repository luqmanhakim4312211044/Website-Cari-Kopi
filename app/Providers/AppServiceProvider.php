<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Dua "gembok" akses (dulu ada di AuthServiceProvider).
        // Admin web: kategori, pengguna, dan semua data. Admin toko: hanya toko miliknya.
        Gate::define('kelola_web', fn (User $user) => $user->isAdmin());
        Gate::define('kelola_toko', fn (User $user) => $user->canManageShop());
        // Semua URL yang dibuat Laravel (asset(), redirect(), form action) memakai https di production.
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        Paginator::useBootstrapFive();

        // Nama pendek untuk relasi polimorfik (disimpan di kolom reviewable_type).
        Relation::morphMap([
            'post' => Post::class,
            'product' => Product::class,
        ]);

        // Isi keranjang untuk navbar: cukup satu tempat, tidak perlu diulang di tiap controller.
        View::composer('layouts.app', function ($view) {
            $items = auth()->check()
                ? auth()->user()->cartItems()->with('product')->get()
                : collect();

            $view->with('navCart', $items);
        });

        // Lencana jumlah pesanan baru di menu dashboard.
        View::composer('layouts.dashboard', function ($view) {
            $user = auth()->user();
            $orders = $user->isAdmin() ? Order::query() : $user->sales();

            $view->with('pendingOrders', $orders->where('status', Order::STATUS_PENDING)->count());
        });
    }
}
