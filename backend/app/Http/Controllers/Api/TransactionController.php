<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockBatch;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Customer;
use App\Models\Debt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'customer', 'items.product']);

        if ($request->start_date) {
            $query->where('transaction_date', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->where('transaction_date', '<=', $request->end_date . ' 23:59:59');
        }

        if ($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->search) {
            $query->where('id', 'LIKE', "%{$request->search}%");
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->get();
        return response()->json($transactions);
    }

    public function show($id)
    {
        $transaction = Transaction::with(['user', 'customer', 'items.product'])->findOrFail($id);
        return response()->json($transaction);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'payment_method' => 'required|string|in:cash,transfer,debt',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            $totalAmount = 0;
            $itemsData = [];

            // 1. Pre-verify stock (ignore expired)
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $totalStock = $product->stockBatches()
                                      ->where('current_qty', '>', 0)
                                      ->where('expired_date', '>', now())
                                      ->sum('current_qty');

                if ($totalStock < $item['qty']) {
                    throw ValidationException::withMessages([
                        "items" => ["Stok produk {$product->name} tidak mencukupi atau kedaluwarsa. Tersedia: {$totalStock}"]
                    ]);
                }
                $totalAmount += $item['qty'] * $item['price'];
            }

            // 2. Handle Debt Limit Check
            if ($validated['payment_method'] === 'debt') {
                if (!$validated['customer_id']) {
                    throw ValidationException::withMessages([
                        "customer_id" => ["Pelanggan harus dipilih untuk metode pembayaran hutang."]
                    ]);
                }

                $customer = Customer::findOrFail($validated['customer_id']);
                if ($customer->current_debt + $totalAmount > $customer->debt_limit) {
                    throw ValidationException::withMessages([
                        "payment_method" => ["Limit hutang pelanggan tidak mencukupi."]
                    ]);
                }
            }

            // 3. Create Transaction
            $transaction = Transaction::create([
                'user_id' => $request->user()->id,
                'customer_id' => $validated['customer_id'],
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'status' => 'completed',
                'transaction_date' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            // 4. Reduce Stock (FIFO) and calculate cost
            foreach ($validated['items'] as $item) {
                $qtyToReduce = $item['qty'];
                $totalCostForItem = 0;
                $product = Product::findOrFail($item['product_id']);
                
                $batches = StockBatch::where('product_id', $item['product_id'])
                    ->where('current_qty', '>', 0)
                    ->where('expired_date', '>', now())
                    ->orderBy('expired_date', 'asc')
                    ->get();

                foreach ($batches as $batch) {
                    if ($qtyToReduce <= 0) break;

                    $deduct = min($batch->current_qty, $qtyToReduce);
                    $batch->decrement('current_qty', $deduct);
                    
                    $totalCostForItem += $deduct * $batch->buy_price;

                    // Log stock reduction per batch
                    $product->stockLogs()->create([
                        'stock_batch_id' => $batch->id,
                        'type' => 'SALE',
                        'quantity' => -$deduct,
                        'reference_id' => (string)$transaction->id,
                        'user_id' => $request->user()->id,
                        'notes' => "Sale transaction #{$transaction->id}",
                    ]);

                    $qtyToReduce -= $deduct;
                }

                $averageCost = $totalCostForItem / $item['qty'];

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'cost_price' => $averageCost,
                    'subtotal' => $item['qty'] * $item['price'],
                ]);

                $product->updateLowStockStatus();
            }

            // 5. Create Debt Record if applicable
            if ($validated['payment_method'] === 'debt') {
                Debt::create([
                    'customer_id' => $validated['customer_id'],
                    'transaction_id' => $transaction->id,
                    'total_amount' => $totalAmount,
                    'remaining_amount' => $totalAmount,
                    'status' => 'unpaid',
                    'due_date' => $validated['due_date'] ?? now()->addDays(30),
                    'notes' => $validated['notes'] ?? null,
                ]);

                $customer->increment('current_debt', $totalAmount);
            }

            return response()->json($transaction->load('items.product'), 201);
        });
    }

    public function profitReport(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        if (!Hash::check($request->password, $request->user()->password)) {
            return response()->json(['message' => 'Password laporan salah.'], 403);
        }

        $startDate = $request->start_date ?? now()->startOfMonth();
        $endDate = $request->end_date ?? now()->endOfDay();

        $stats = TransactionItem::whereHas('transaction', function($query) use ($startDate, $endDate) {
                $query->whereBetween('transaction_date', [$startDate, $endDate]);
            })
            ->select(
                DB::raw('SUM(subtotal) as total_revenue'),
                DB::raw('SUM(qty * cost_price) as total_cost'),
                DB::raw('SUM(subtotal - (qty * cost_price)) as total_profit')
            )
            ->first();

        return response()->json([
            'total_revenue' => (float)$stats->total_revenue,
            'total_cost' => (float)$stats->total_cost,
            'total_profit' => (float)$stats->total_profit,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    public function detailedReport(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth();
        $endDate = $request->end_date ?? now()->endOfDay();

        // 1. Sales by Product
        $salesByProduct = TransactionItem::whereHas('transaction', function($query) use ($startDate, $endDate) {
                $query->whereBetween('transaction_date', [$startDate, $endDate]);
            })
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->select(
                'products.name',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('SUM(subtotal) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_revenue', 'desc')
            ->get();

        // 2. Sales by Category
        $salesByCategory = TransactionItem::whereHas('transaction', function($query) use ($startDate, $endDate) {
                $query->whereBetween('transaction_date', [$startDate, $endDate]);
            })
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                DB::raw('COALESCE(categories.name, "Uncategorized") as category_name'),
                DB::raw('SUM(subtotal) as total_revenue')
            )
            ->groupBy('category_name')
            ->orderBy('total_revenue', 'desc')
            ->get();

        // 3. Sales by Payment Method
        $salesByPayment = Transaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->select(
                'payment_method',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_amount) as total_revenue')
            )
            ->groupBy('payment_method')
            ->get();

        return response()->json([
            'sales_by_product' => $salesByProduct,
            'sales_by_category' => $salesByCategory,
            'sales_by_payment' => $salesByPayment,
            'total_revenue' => $salesByProduct->sum('total_revenue'),
            'total_transactions' => Transaction::whereBetween('transaction_date', [$startDate, $endDate])->count(),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }
}
