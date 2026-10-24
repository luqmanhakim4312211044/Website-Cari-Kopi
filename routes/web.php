<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Publik
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/cafes/{post}', [HomeController::class, 'show'])->whereNumber('post')->name('home.show');
Route::get('/products/{product}', [ProductController::class, 'show'])->whereNumber('product')->name('product.show');

/*
|--------------------------------------------------------------------------
| Tamu (belum login)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.verify');
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
});

/*
|--------------------------------------------------------------------------
| Pengguna login
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('profile', [ProfileController::class, 'show'])->name('profile');
    Route::patch('password', [ProfileController::class, 'updatePassword'])->name('password.update');

    // Keranjang
    Route::get('cart', [CartController::class, 'index'])->name('cart');
    Route::post('cart/{product}', [CartController::class, 'add'])->whereNumber('product')->name('cart.add');
    Route::patch('cart/{cart}', [CartController::class, 'update'])->whereNumber('cart')->name('cart.update');
    Route::delete('cart/{cart}', [CartController::class, 'destroy'])->whereNumber('cart')->name('cart.destroy');

    // Pesanan
    Route::resource('orders', OrderController::class)->only(['index', 'create', 'store', 'show']);

    // Ulasan
    Route::post('cafes/{post}/reviews', [ReviewController::class, 'storeForPost'])->whereNumber('post')->middleware('throttle:10,1')->name('reviews.post');
    Route::post('products/{product}/reviews', [ReviewController::class, 'storeForProduct'])->whereNumber('product')->middleware('throttle:10,1')->name('reviews.product');
    Route::patch('reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin toko & admin web: dashboard, cafe, produk, pesanan masuk
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'can:kelola_toko'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/orders', [DashboardController::class, 'orders'])->name('dashboard.orders');
    Route::patch('dashboard/orders/{order}', [DashboardController::class, 'updateOrder'])->whereNumber('order')->name('dashboard.orders.update');

    Route::get('dashboard/report', [ReportController::class, 'show'])->name('dashboard.report');
    Route::get('dashboard/report/pdf', [ReportController::class, 'pdf'])->name('dashboard.report.pdf');

    Route::resource('posts', PostController::class)->except('show');
    Route::resource('products', ProductController::class)->except('show');
});

/*
|--------------------------------------------------------------------------
| Khusus admin web: kategori & pengguna
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'can:kelola_web'])->group(function () {
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('users', UserController::class)->only(['index', 'edit', 'update', 'destroy']);
});
