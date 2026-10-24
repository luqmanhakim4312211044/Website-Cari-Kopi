<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun awal. GANTI passwordnya setelah login pertama.
        $this->account('admin@carikopi.test', 'Admin Web', User::ROLE_ADMIN);
        $this->account('toko@carikopi.test', 'Admin Toko', User::ROLE_OWNER);

        foreach (['Kafe', 'Kedai Kopi', 'Roastery', 'Coworking Cafe'] as $title) {
            Category::firstOrCreate(['title' => $title]);
        }
    }

    private function account(string $email, string $name, string $role): void
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make('password')]
        );
        $user->forceFill(['role' => $role])->save();
    }
}
