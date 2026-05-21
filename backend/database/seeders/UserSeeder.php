<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Owner Toko',
            'username' => 'owner',
            'password' => Hash::make('password123'),
            'role' => 'owner',
        ]);

        User::create([
            'name' => 'Admin Toko',
            'username' => 'admin',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Kasir 1',
            'username' => 'kasir1',
            'password' => Hash::make('password123'),
            'role' => 'kasir',
        ]);
    }
}
