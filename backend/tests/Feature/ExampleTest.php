<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_restock_persists_in_database(): void
    {
        // 1. Create owner user
        $user = User::create([
            'name' => 'Owner Toko',
            'username' => 'owner',
            'password' => bcrypt('password123'),
            'role' => 'owner',
        ]);

        // 2. Create category and product
        $category = Category::create(['name' => 'Beras']);
        $product = Product::create([
            'name' => 'Beras Cianjur',
            'sku' => 'BRS-001',
            'category' => 'Beras',
            'category_id' => $category->id,
            'unit' => 'kg',
            'min_stock' => 5,
        ]);

        // 3. Act: Post restock request
        $response = $this->actingAs($user)
            ->postJson("/api/products/{$product->id}/stock", [
                'batch_number' => 'BCH-TEST-999',
                'qty' => 50,
                'buy_price' => 10000,
                'sell_price' => 12000,
                'expired_date' => '',
                'rack_location' => 'Rak A1',
            ]);

        // 4. Assert: Check response status
        $response->assertStatus(201);

        // 5. Assert: Check if stock batch exists in database
        $this->assertDatabaseHas('stock_batches', [
            'product_id' => $product->id,
            'batch_number' => 'BCH-TEST-999',
            'qty' => 50,
            'current_qty' => 50,
            'buy_price' => 10000,
            'sell_price' => 12000,
        ]);

        // 6. Assert: Check if stock log exists
        $this->assertDatabaseHas('stock_logs', [
            'product_id' => $product->id,
            'type' => 'RESTOCK',
            'quantity' => 50,
        ]);

        // 7. Assert: Check if product total stock is updated and min stock status is false (not low stock)
        $product->refresh();
        $this->assertEquals(50, $product->total_stock);
        $this->assertFalse((bool)$product->is_low_stock);
    }

    public function test_restock_without_optional_fields_persists_in_database(): void
    {
        $user = User::create([
            'name' => 'Owner Toko 2',
            'username' => 'owner2',
            'password' => bcrypt('password123'),
            'role' => 'owner',
        ]);

        $category = Category::create(['name' => 'Minyak']);
        $product = Product::create([
            'name' => 'Minyak Goreng Bimoli',
            'sku' => 'MYK-001',
            'category' => 'Minyak',
            'category_id' => $category->id,
            'unit' => 'liter',
            'min_stock' => 5,
        ]);

        // Post restock request WITHOUT expired_date and rack_location
        $response = $this->actingAs($user)
            ->postJson("/api/products/{$product->id}/stock", [
                'batch_number' => 'BCH-TEST-888',
                'qty' => 30,
                'buy_price' => 15000,
                'sell_price' => 18000,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('stock_batches', [
            'product_id' => $product->id,
            'batch_number' => 'BCH-TEST-888',
            'qty' => 30,
            'current_qty' => 30,
            'buy_price' => 15000,
            'sell_price' => 18000,
            'expired_date' => null,
            'rack_location' => null,
        ]);
    }

    public function test_profit_report_unlock(): void
    {
        $user = User::create([
            'name' => 'Owner Toko',
            'username' => 'owner',
            'password' => 'password123',
            'role' => 'owner',
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/reports/profit", [
                'password' => 'password123',
            ]);

        $response->assertStatus(200);
    }

    public function test_seeded_owner_unlock(): void
    {
        $this->seed(\Database\Seeders\UserSeeder::class);
        $user = User::where('username', 'owner')->first();
        
        $response = $this->actingAs($user)
            ->postJson("/api/reports/profit", [
                'password' => 'password123',
            ]);

        $response->assertStatus(200);
    }

    public function test_store_financial_report(): void
    {
        $user = User::create([
            'name' => 'Owner Toko',
            'username' => 'owner',
            'password' => 'password123',
            'role' => 'owner',
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/financial-reports", [
                'report_date' => '2026-06-03',
                'total_revenue' => 500000,
                'total_cost' => 400000,
                'total_profit' => 100000,
                'transaction_count' => 10,
                'new_debt_amount' => 50000,
                'debt_payments_received' => 20000,
                'expense_amount' => 10000,
                'net_income' => 110000,
                'created_by_user' => 'Owner Toko',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('financial_reports', [
            'report_date' => '2026-06-03 00:00:00',
            'expense_amount' => 10000,
            'net_income' => 110000,
        ]);
    }

    public function test_transaction_with_null_expired_date_stock(): void
    {
        $user = User::create([
            'name' => 'Kasir Toko',
            'username' => 'kasir',
            'password' => bcrypt('password123'),
            'role' => 'kasir',
        ]);

        $category = Category::create(['name' => 'Beras']);
        $product = Product::create([
            'name' => 'Beras Cianjur',
            'sku' => 'BRS-001',
            'category' => 'Beras',
            'category_id' => $category->id,
            'unit' => 'kg',
            'min_stock' => 5,
        ]);

        // Restock with expired_date as null
        $product->stockBatches()->create([
            'batch_number' => 'BCH-NULL-EXP',
            'qty' => 10,
            'current_qty' => 10,
            'buy_price' => 10000,
            'sell_price' => 12000,
            'expired_date' => null,
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/transactions", [
                'payment_method' => 'cash',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'qty' => 3,
                        'price' => 12000,
                    ]
                ]
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('stock_batches', [
            'product_id' => $product->id,
            'batch_number' => 'BCH-NULL-EXP',
            'current_qty' => 7, // 10 - 3
        ]);
    }
}

