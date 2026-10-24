<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nama');
            $table->string('email');
            $table->string('nomor', 20);              // string: nomor telepon bisa diawali 0 / +62
            $table->string('alamat');
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('diskon')->default(0);
            $table->unsignedInteger('total');
            $table->string('status')->default('pending'); // pending | paid
            $table->string('gambar')->nullable();         // bukti pembayaran
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nama');
            $table->unsignedInteger('harga');
            $table->unsignedSmallInteger('jumlah');
            $table->unsignedInteger('total');
            $table->string('gambar')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
