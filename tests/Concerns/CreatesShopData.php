<?php

namespace Tests\Concerns;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Post;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;

/** Pembuat data contoh untuk tes (pengguna, cafe, produk, keranjang, pesanan, ulasan). */
trait CreatesShopData
{
    private int $sequence = 0;

    protected function makeUser(string $role = User::ROLE_CUSTOMER, string $member = 'none'): User
    {
        $this->sequence++;

        $user = User::create([
            'name' => "Pengguna {$this->sequence}",
            'email' => "pengguna{$this->sequence}@example.test",
            'password' => bcrypt('password-123'),
        ]);

        // role & member sengaja tidak fillable, jadi diisi eksplisit.
        $user->forceFill(['role' => $role, 'member' => $member])->save();

        return $user;
    }

    protected function makeCategory(?string $title = null): Category
    {
        $this->sequence++;

        return Category::create(['title' => $title ?? "Kategori {$this->sequence}"]);
    }

    protected function makeShop(User $owner, ?Category $category = null, array $attributes = []): Post
    {
        $this->sequence++;

        return Post::create(array_merge([
            'user_id' => $owner->id,
            'category_id' => ($category ?? $this->makeCategory())->id,
            'title' => "Cafe {$this->sequence}",
            'body' => 'Tempat nongkrong yang nyaman.',
            'latitude' => 1.1234567,
            'longitude' => 104.0305,
            'provinsi' => 'Kepulauan Riau',
            'kota' => 'Batam',
            'alamat' => 'Jl. Contoh No. 1',
        ], $attributes));
    }

    protected function makeProduct(Post $shop, int $price = 10000, array $attributes = []): Product
    {
        $this->sequence++;

        return Product::create(array_merge([
            'user_id' => $shop->user_id,
            'post_id' => $shop->id,
            'title' => "Produk {$this->sequence}",
            'body' => 'Enak dan segar.',
            'price' => $price,
        ], $attributes));
    }

    protected function makeCart(User $user, Product $product, int $quantity = 1): Cart
    {
        return Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
        ]);
    }

    protected function makeOrder(User $buyer, User $seller, string $status = Order::STATUS_PENDING, int $total = 10000): Order
    {
        return Order::create([
            'user_id' => $buyer->id,
            'seller_id' => $seller->id,
            'nama' => $buyer->name,
            'email' => $buyer->email,
            'nomor' => '081234567890',
            'alamat' => 'Jl. Contoh No. 1',
            'subtotal' => $total,
            'diskon' => 0,
            'total' => $total,
            'status' => $status,
        ]);
    }

    protected function makeReview(User $user, Post|Product $reviewable, int $rating = 5, ?string $comment = 'Mantap'): Review
    {
        return $reviewable->reviews()->create([
            'user_id' => $user->id,
            'rating' => $rating,
            'comment' => $comment,
        ]);
    }
}
