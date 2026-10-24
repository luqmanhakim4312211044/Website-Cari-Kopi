<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Data demo: 10 cafe fiktif di Batam (3-10 produk per cafe), 10 akun pelanggan,
 * dan ulasan acak dari akun-akun itu.
 *
 * Jalankan:  php artisan db:seed --class=DummyDataSeeder
 *
 * Nama cafe & alamat adalah karangan; koordinat hanya perkiraan area di Batam.
 * Gambar adalah ilustrasi buatan sendiri di database/seeders/dummy-images (ganti dengan foto bebas lisensi bila mau).
 * Acaknya memakai seed tetap, jadi hasilnya sama setiap kali dijalankan pada database yang bersih.
 */
class DummyDataSeeder extends Seeder
{
    private const FIRST_NAMES = ['Andi', 'Budi', 'Citra', 'Dewi', 'Eko', 'Fitri', 'Gita', 'Hendra', 'Indah', 'Joko', 'Kartika', 'Lestari', 'Mega', 'Nanda', 'Putra', 'Rina', 'Siti', 'Teguh', 'Wahyu', 'Yuni', 'Rizky', 'Aulia', 'Dimas', 'Nabila', 'Fajar', 'Salsa'];

    private const LAST_NAMES = ['Pratama', 'Saputra', 'Wijaya', 'Lestari', 'Hidayat', 'Kusuma', 'Nugroho', 'Santoso', 'Permata', 'Rahman', 'Siregar', 'Simanjuntak', 'Hutapea', 'Harahap', 'Putri', 'Ramadhan', 'Firmansyah', 'Utami'];

    /** [nama, area, jalan (karangan), latitude, longitude] */
    private const SHOPS = [
        ['Kopi Senja Nagoya', 'Nagoya', 'Jl. Imam Bonjol', 1.1450, 104.0300],
        ['Ruang Seduh', 'Batam Centre', 'Jl. Engku Putri', 1.1300, 104.0530],
        ['Biji & Bara', 'Nongsa', 'Jl. Hang Lekir', 1.1860, 104.0900],
        ['Warung Kopi Laut', 'Harbour Bay', 'Jl. Duyung', 1.1560, 104.0050],
        ['Teras Nongsa', 'Nongsa', 'Jl. Pantai Indah', 1.1830, 104.0860],
        ['Sudut Pelabuhan Coffee', 'Jodoh', 'Jl. Raja H. Fisabilillah', 1.1370, 104.0180],
        ['Kopi Tiga Pulau', 'Sekupang', 'Jl. Sultan Abdul Rahman', 1.1180, 103.9370],
        ['Rumah Roasting Batam', 'Bengkong', 'Jl. Laksamana Bintan', 1.1280, 104.0790],
        ['Nusa Brew Co.', 'Tiban', 'Jl. Gajah Mada', 1.0990, 103.9830],
        ['Meja Bersama Coworking', 'Lubuk Baja', 'Jl. Yos Sudarso', 1.1430, 104.0180],
    ];

    private const SHOP_BODIES = [
        'Kedai santai dengan biji kopi pilihan dan suasana yang cocok untuk bekerja atau berkumpul bersama teman.',
        'Tempat nyaman dengan pilihan kopi manual brew, camilan, dan colokan di hampir setiap meja.',
        'Kafe dengan teras luas dan angin sore yang enak, favorit untuk menikmati kopi sambil bersantai.',
        'Menyajikan kopi susu khas lokal, kue buatan sendiri, dan musik akustik di akhir pekan.',
        'Ruang kerja bersama dengan kopi yang enak, wifi kencang, dan area tenang untuk fokus.',
    ];

    /** [nama, deskripsi, harga minimum, harga maksimum, kunci gambar] */
    private const PRODUCTS = [
        ['Espresso', 'Satu shot espresso pekat dengan crema tebal.', 12000, 18000, 'hot_coffee'],
        ['Americano', 'Espresso dengan air panas, ringan dan bersih.', 15000, 22000, 'hot_coffee'],
        ['Cappuccino', 'Espresso, susu panas, dan busa susu yang tebal.', 20000, 28000, 'latte_art'],
        ['Caffe Latte', 'Espresso dengan susu steam yang lembut.', 22000, 30000, 'latte_art'],
        ['Flat White', 'Rasa kopi lebih kuat dengan susu microfoam.', 24000, 32000, 'latte_art'],
        ['Mocha', 'Espresso, cokelat, dan susu dalam satu cangkir.', 24000, 32000, 'latte_art'],
        ['Kopi Susu Gula Aren', 'Kopi susu dengan manis gula aren yang pas.', 18000, 26000, 'iced_coffee'],
        ['Es Kopi Susu', 'Kopi susu dingin, segar dan creamy.', 18000, 25000, 'iced_coffee'],
        ['Cold Brew', 'Diseduh dingin 16 jam, halus dan rendah asam.', 25000, 35000, 'iced_coffee'],
        ['Affogato', 'Es krim vanila disiram espresso panas.', 26000, 36000, 'iced_coffee'],
        ['Vietnam Drip', 'Kopi seduh tetes dengan susu kental manis.', 20000, 28000, 'hot_coffee'],
        ['Manual Brew V60', 'Seduhan manual dari biji pilihan hari ini.', 25000, 38000, 'hot_coffee'],
        ['Cokelat Panas', 'Cokelat pekat dengan susu panas.', 20000, 28000, 'hot_choco'],
        ['Matcha Latte', 'Matcha dengan susu, manis dan segar.', 25000, 35000, 'iced_matcha'],
        ['Teh Tarik', 'Teh susu yang ditarik hingga berbusa.', 15000, 22000, 'tea'],
        ['Lemon Tea', 'Teh dengan perasan lemon, segar diminum dingin maupun panas.', 14000, 20000, 'tea'],
        ['Croissant Butter', 'Croissant renyah dengan aroma mentega.', 18000, 28000, 'croissant'],
        ['Donat Cokelat', 'Donat empuk dengan lapisan cokelat.', 10000, 16000, 'donut'],
        ['Cheesecake', 'Kue keju lembut dengan dasar biskuit.', 28000, 38000, 'cake'],
        ['Red Velvet Cake', 'Kue red velvet dengan krim keju.', 28000, 38000, 'cake'],
        ['Brownies', 'Brownies cokelat padat dan legit.', 15000, 25000, 'cake_choco'],
        ['Banana Bread', 'Roti pisang hangat yang manis alami.', 16000, 24000, 'cake_choco'],
        ['Roti Bakar Cokelat Keju', 'Roti bakar tebal dengan cokelat dan keju.', 15000, 24000, 'sandwich'],
        ['Sandwich Telur', 'Sandwich isi telur, sayur, dan keju.', 22000, 32000, 'sandwich'],
    ];

    private const SHOP_COMMENTS = [
        5 => ['Suasananya nyaman banget, cocok untuk kerja seharian.', 'Kopinya enak dan pelayanannya ramah. Pasti balik lagi!', 'Tempat favorit baru. Wifi kencang, kursinya nyaman.', 'Barista-nya paham banget soal kopi. Recommended!'],
        4 => ['Enak dan tempatnya bersih, hanya agak ramai di akhir pekan.', 'Pilihan menunya banyak, harganya masih masuk akal.', 'Tempatnya asyik, parkirannya agak terbatas.'],
        3 => ['Lumayan, tapi antreannya cukup lama.', 'Rasanya standar, tempatnya oke.', 'Cukup nyaman, tapi kadang terlalu bising.'],
        2 => ['Pelayanan agak lambat dan tempatnya panas.', 'Harga kurang sebanding dengan porsinya.'],
        1 => ['Kecewa, pesanan salah dan lama sekali datang.'],
    ];

    private const PRODUCT_COMMENTS = [
        5 => ['Enak banget, porsinya pas!', 'Rasanya mantap, sudah beberapa kali pesan.', 'Juara! Wajib dicoba kalau ke sini.', 'Segar dan tidak terlalu manis, suka.'],
        4 => ['Enak, tapi manisnya sedikit kebanyakan.', 'Rasanya enak, harganya lumayan.', 'Bagus, cuma porsinya bisa lebih banyak.'],
        3 => ['Biasa saja, tidak istimewa.', 'Oke, tapi masih ada yang lebih enak.'],
        2 => ['Kurang sesuai ekspektasi.', 'Terlalu pahit untuk selera saya.'],
        1 => ['Rasanya aneh, tidak akan pesan lagi.'],
    ];

    public function run(): void
    {
        if (User::where('email', 'test1@carikopi.test')->exists()) {
            $this->command->warn('Data dummy sudah ada (test1@carikopi.test ditemukan). Dilewati. Gunakan migrate:fresh --seed untuk mengulang dari awal.');

            return;
        }

        mt_srand(20261017); // acak, tapi hasilnya sama setiap dijalankan pada database bersih

        $this->call(DatabaseSeeder::class); // akun admin, admin toko, dan kategori

        $owners = [
            User::where('email', 'toko@carikopi.test')->firstOrFail(),
            $this->account('toko2@carikopi.test', 'Admin Toko 2', User::ROLE_OWNER),
        ];

        $customers = $this->createCustomers();
        $posts = $this->createShops($owners);
        $reviewCount = $this->createReviews($customers, $posts);

        $this->command->info(sprintf(
            'Selesai: %d toko, %d produk, %d pelanggan (test1..test10@carikopi.test, password "password"), %d ulasan.',
            count($posts),
            Product::count(),
            count($customers),
            $reviewCount
        ));
    }

    /* ---------- Akun ---------- */

    private function account(string $email, string $name, string $role): User
    {
        $user = User::firstOrCreate(['email' => $email], ['name' => $name, 'password' => Hash::make('password')]);
        $user->forceFill(['role' => $role])->save();

        return $user;
    }

    /** @return User[] */
    private function createCustomers(): array
    {
        $members = array_keys(User::MEMBER_DISCOUNTS);
        $customers = [];

        for ($i = 1; $i <= 10; $i++) {
            $user = User::create([
                'name' => $this->pick(self::FIRST_NAMES).' '.$this->pick(self::LAST_NAMES),
                'email' => "test{$i}@carikopi.test",
                'password' => Hash::make('password'),
            ]);
            $user->forceFill(['role' => User::ROLE_CUSTOMER, 'member' => $this->pick($members)])->save();

            $customers[] = $user;
        }

        return $customers;
    }

    /* ---------- Toko & produk ---------- */

    /** @return Post[] */
    private function createShops(array $owners): array
    {
        $categories = Category::all()->all();
        $posts = [];

        foreach (self::SHOPS as $i => [$name, $area, $street, $lat, $lng]) {
            $post = Post::create([
                'user_id' => $owners[$i < 6 ? 0 : 1]->id,
                'category_id' => $this->pick($categories)->id,
                'title' => $name,
                'body' => $this->pick(self::SHOP_BODIES),
                'gambar' => $this->copyImage(sprintf('cafe-%02d.jpg', $i + 1)),
                'latitude' => $lat + mt_rand(-20, 20) / 10000,
                'longitude' => $lng + mt_rand(-20, 20) / 10000,
                'provinsi' => 'Kepulauan Riau',
                'kota' => 'Batam',
                'alamat' => $street.' No. '.mt_rand(1, 99).', '.$area,
            ]);

            foreach ($this->pickMany(self::PRODUCTS, mt_rand(3, 10)) as [$title, $body, $min, $max, $imageKey]) {
                Product::create([
                    'user_id' => $post->user_id,   // penjual = pemilik cafe
                    'post_id' => $post->id,
                    'title' => $title,
                    'body' => $body,
                    'gambar' => $this->copyImage("product-{$imageKey}.jpg"),
                    'price' => mt_rand((int) ($min / 500), (int) ($max / 500)) * 500,
                ]);
            }

            $posts[] = $post->load('products');
        }

        return $posts;
    }

    /* ---------- Ulasan ---------- */

    /**
     * Akun pertama mengulas 3 toko dan 5 produk dari toko-toko itu.
     * Akun lain mengulas 1-5 toko dan 1-8 produk (dari toko yang ia ulas).
     */
    private function createReviews(array $customers, array $posts): int
    {
        $total = 0;

        foreach ($customers as $index => $customer) {
            $shopCount = $index === 0 ? 3 : mt_rand(1, 5);
            $productCount = $index === 0 ? 5 : mt_rand(1, 8);

            $shops = $this->pickMany($posts, $shopCount);
            foreach ($shops as $shop) {
                $this->review($customer, $shop, self::SHOP_COMMENTS);
                $total++;
            }

            $candidates = [];
            foreach ($shops as $shop) {
                foreach ($shop->products as $product) {
                    $candidates[] = $product;
                }
            }

            foreach ($this->pickMany($candidates, min($productCount, count($candidates))) as $product) {
                $this->review($customer, $product, self::PRODUCT_COMMENTS);
                $total++;
            }
        }

        return $total;
    }

    private function review(User $user, Model $reviewable, array $comments): void
    {
        // Cenderung positif: 5% bintang 1, 10% bintang 2, 20% bintang 3, 35% bintang 4, 30% bintang 5.
        $roll = mt_rand(1, 100);
        $rating = match (true) {
            $roll <= 5 => 1,
            $roll <= 15 => 2,
            $roll <= 35 => 3,
            $roll <= 70 => 4,
            default => 5,
        };

        $review = $reviewable->reviews()->make([
            'user_id' => $user->id,
            'rating' => $rating,
            'comment' => mt_rand(1, 100) <= 15 ? null : $this->pick($comments[$rating]),
        ]);
        $review->created_at = $review->updated_at = now()->subMinutes(mt_rand(60, 60 * 24 * 45));
        $review->save();
    }

    /* ---------- Bantuan ---------- */

    /** Setiap baris mendapat salinan file sendiri, supaya menghapus satu produk tidak merusak gambar produk lain. */
    private function copyImage(string $file): string
    {
        $path = 'gambarku/seed-'.Str::lower(Str::random(12)).'.jpg';
        Storage::disk('public')->put($path, file_get_contents(database_path('seeders/dummy-images/'.$file)));

        return $path;
    }

    private function pick(array $items)
    {
        $items = array_values($items);

        return $items[mt_rand(0, count($items) - 1)];
    }

    private function pickMany(array $items, int $count): array
    {
        $items = array_values($items);

        for ($i = count($items) - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            [$items[$i], $items[$j]] = [$items[$j], $items[$i]];
        }

        return array_slice($items, 0, $count);
    }
}
