<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Pemilik cafe yang menjual produk di pesanan ini.
            $table->foreignId('seller_id')->nullable()->after('user_id')->constrained('users')->cascadeOnDelete();
        });

        // Isi pesanan lama: penjual = pemilik produk pertama di pesanan itu.
        DB::table('orders')->orderBy('id')->each(function ($order) {
            $sellerId = DB::table('order_items')
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->where('order_items.order_id', $order->id)
                ->value('products.user_id');

            if ($sellerId) {
                DB::table('orders')->where('id', $order->id)->update(['seller_id' => $sellerId]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('seller_id');
        });
    }
};
