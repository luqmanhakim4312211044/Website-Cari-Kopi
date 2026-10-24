<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menyamakan skema dengan model & controller (khusus MySQL/MariaDB):
 *  - details: nama/total/gambar -> nama2/total2/gambar2. Database development
 *    sudah diubah manual, jadi tiap langkah dicek dulu (aman dijalankan di
 *    database lama maupun baru).
 *  - orders.nomor: INT -> VARCHAR (nomor HP 12 digit melebihi batas INT dan
 *    angka 0 di depan hilang).
 *  - orders.status: default 'belom'.
 */
return new class extends Migration
{
    public function up()
    {
        // Migrasi ini hanya memperbaiki database LAMA (MySQL) yang masih punya tabel `details`.
        // Database baru dari migrasi di atas sudah punya skema yang benar; tanpa pengecekan ini,
        // nilai bawaan `orders.status` malah berubah menjadi 'belom' dan perintah MySQL gagal di SQLite.
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasTable('details')) {
            return;
        }

        if (Schema::hasColumn('details', 'nama')) {
            DB::statement('ALTER TABLE `details` CHANGE `nama` `nama2` VARCHAR(255) NOT NULL');
        }
        if (Schema::hasColumn('details', 'total')) {
            DB::statement('ALTER TABLE `details` CHANGE `total` `total2` INT NOT NULL');
        }
        if (Schema::hasColumn('details', 'gambar')) {
            DB::statement('ALTER TABLE `details` CHANGE `gambar` `gambar2` VARCHAR(255) NULL');
        } elseif (Schema::hasColumn('details', 'gambar2')) {
            DB::statement('ALTER TABLE `details` MODIFY `gambar2` VARCHAR(255) NULL');
        }

        DB::statement('ALTER TABLE `orders` MODIFY `nomor` VARCHAR(20) NOT NULL');
        DB::statement("ALTER TABLE `orders` MODIFY `status` VARCHAR(255) NOT NULL DEFAULT 'belom'");
    }

    public function down()
    {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasTable('details')) {
            return;
        }

        // Sengaja tidak dikembalikan: mengembalikan nomor ke INT akan merusak data nomor HP.
        DB::statement('ALTER TABLE `orders` MODIFY `status` VARCHAR(255) NOT NULL');
    }
};
