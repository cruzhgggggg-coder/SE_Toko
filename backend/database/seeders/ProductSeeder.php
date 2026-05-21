<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockBatch;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $p1 = Product::create([
            'name' => 'Beras Pandan Wangi 5kg',
            'sku' => 'BRS-001',
            'category' => 'Sembako',
            'unit' => 'karung',
            'min_stock' => 5,
            'image' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?q=80&w=2070&auto=format&fit=crop'
        ]);

        StockBatch::create([
            'product_id' => $p1->id,
            'batch_number' => 'BCH-001',
            'qty' => 10,
            'current_qty' => 10,
            'buy_price' => 60000,
            'sell_price' => 75000,
            'expired_date' => '2027-01-01',
        ]);

        $p2 = Product::create([
            'name' => 'Minyak Goreng Bimoli 2L',
            'sku' => 'MNY-001',
            'category' => 'Sembako',
            'unit' => 'pouch',
            'min_stock' => 10,
            'image' => 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?q=80&w=2070&auto=format&fit=crop'
        ]);

        StockBatch::create([
            'product_id' => $p2->id,
            'batch_number' => 'BCH-002',
            'qty' => 20,
            'current_qty' => 20,
            'buy_price' => 30000,
            'sell_price' => 38000,
            'expired_date' => '2026-12-01',
        ]);

        $p3 = Product::create([
            'name' => 'Indomie Goreng Original',
            'sku' => 'IDM-001',
            'category' => 'Mie Instan',
            'unit' => 'bungkus',
            'min_stock' => 50,
            'image' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?q=80&w=2070&auto=format&fit=crop'
        ]);

        StockBatch::create([
            'product_id' => $p3->id,
            'batch_number' => 'BCH-003',
            'qty' => 100,
            'current_qty' => 85,
            'buy_price' => 2800,
            'sell_price' => 3500,
            'expired_date' => '2026-06-01',
        ]);
    }
}
