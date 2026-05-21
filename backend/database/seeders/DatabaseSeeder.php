<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed default shop setting
        Setting::updateOrCreate(['key' => 'shop_name'], ['value' => 'Toko Sumber Makmur']);
        Setting::updateOrCreate(['key' => 'shop_address'], ['value' => 'Jl. Kebon Jeruk No. 123']);
        Setting::updateOrCreate(['key' => 'shop_phone'], ['value' => '08123456789']);

        // Seed default categories
        $categories = [
            'Beras',
            'Minyak',
            'Gula',
            'Tepung',
            'Mie & Pasta',
            'Minuman',
            'Sabun & Detergen',
            'Rokok'
        ];

        foreach ($categories as $catName) {
            Category::firstOrCreate([
                'name' => $catName
            ], [
                'description' => "Kategori untuk produk {$catName}"
            ]);
        }

        $this->call([
            UserSeeder::class,
            ProductSeeder::class,
            CustomerSeeder::class,
        ]);
    }
}

