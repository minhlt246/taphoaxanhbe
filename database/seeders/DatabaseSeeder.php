<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Chạy UserSeeder để tạo 50 user ngẫu nhiên
        $this->call([
            UserSeeder::class,
            VoucherSeeder::class,
            OrderSeeder::class,
            ReviewSeeder::class,
        ]);
    }
}
