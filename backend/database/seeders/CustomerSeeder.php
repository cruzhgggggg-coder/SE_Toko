<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        Customer::create([
            'name' => 'Budi Santoso',
            'phone' => '08123456789',
            'address' => 'Jl. Merdeka No. 10',
            'debt_limit' => 500000,
        ]);

        Customer::create([
            'name' => 'Siti Aminah',
            'phone' => '08987654321',
            'address' => 'Jl. Mawar No. 5',
            'debt_limit' => 1000000,
        ]);
    }
}
