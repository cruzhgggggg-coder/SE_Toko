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

        if ($request->filled('start_date')) {
            $query->where('transaction_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('transaction_date', '<=', $request->end_date . ' 23:59:59');
        }

        if ($request->filled('customer_id') && is_numeric($request->customer_id)) {
            $query->where('customer_id', (int) $request->customer_id);
        }

        // FIX: Escape LIKE wildcards to prevent SQL LIKE injection
        if ($request->filled('search')) {
            $search = addcslashes($request->input('search'), '%_\\');
            $query->where('id', 'LIKE', "%{$search}%");
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->get();
        return response()->json($transactions);
    }

    public function show($id)
    {
        if (!is_numeric($id)) {
            return response()->json(['message' => 'ID transaksi tidak valid.'], 422);
        }

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
            'items.*.qty' => 'required|integer|min:1|max:10000',
            'items.*.price' => 'required|numeric|min:0|max:999999999',
            'notes' => 'nullable|string|max:500',
            'due_date' => 'nullable|date|after_or_equal:today',
            'cash_paid' => 'nullable|numeric|min:0|max:999999999',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            $totalAmount = 0;
            $itemsData = [];

            // 1. Pre-verify stock with pessimistic locking to prevent race conditions
            foreach ($validated['items'] as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                $totalStock = $product->stockBatches()
                    ->where('current_qty', '>', 0)
                    ->where(function ($q) {
                        $q->where('expired_date', '>', now())
                          ->orWhereNull('expired_date');
                    })
                    ->lockForUpdate()
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
                if (empty($validated['customer_id'])) {
                    throw ValidationException::withMessages([
                        "customer_id" => ["Pelanggan harus dipilih untuk metode pembayaran hutang."]
                    ]);
                }

                $customer = Customer::lockForUpdate()->findOrFail($validated['customer_id']);
                if ($customer->current_debt + $totalAmount > $customer->debt_limit) {
                    throw ValidationException::withMessages([
                        "payment_method" => ["Limit hutang pelanggan tidak mencukupi."]
                    ]);
                }
            }

            // 3. Create Transaction
            $transaction = Transaction::create([
                'user_id' => $request->user()->id,
                'customer_id' => $validated['customer_id'] ?? null,
                'total_amount' => $totalAmount,
                'cash_paid' => $validated['cash_paid'] ?? null,
                'payment_method' => $validated['payment_method'],
                'status' => 'completed',
                'transaction_date' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            // 4. Reduce Stock (FIFO) with locked batches to prevent race conditions
            foreach ($validated['items'] as $item) {
                $qtyToReduce = $item['qty'];
                $totalCostForItem = 0;
                $product = Product::findOrFail($item['product_id']);

                $batches = StockBatch::where('product_id', $item['product_id'])
                    ->where('current_qty', '>', 0)
                    ->where(function ($q) {
                        $q->where('expired_date', '>', now())
                          ->orWhereNull('expired_date');
                    })
                    ->orderByRaw('CASE WHEN expired_date IS NULL THEN 1 ELSE 0 END, expired_date ASC')
                    ->lockForUpdate()
                    ->get();

                foreach ($batches as $batch) {
                    if ($qtyToReduce <= 0) break;

                    $deduct = min($batch->current_qty, $qtyToReduce);
                    $batch->decrement('current_qty', $deduct);

                    $totalCostForItem += $deduct * $batch->buy_price;

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

                // Prevent division by zero
                $averageCost = $item['qty'] > 0 ? $totalCostForItem / $item['qty'] : 0;

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
                    'amount' => $totalAmount,
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

        $owner = \App\Models\User::where('role', 'owner')->first();
        $passwordHash = $owner ? $owner->password : $request->user()->password;

        if (!Hash::check($request->password, $passwordHash)) {
            return response()->json(['message' => 'Password laporan salah.'], 403);
        }

        $startDate = $request->start_date
            ? \Carbon\Carbon::parse($request->start_date)->startOfDay()->toDateTimeString()
            : \Carbon\Carbon::now()->startOfMonth()->startOfDay()->toDateTimeString();
        $endDate = $request->end_date
            ? \Carbon\Carbon::parse($request->end_date)->endOfDay()->toDateTimeString()
            : \Carbon\Carbon::now()->endOfDay()->toDateTimeString();

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
            'total_revenue' => (float)($stats->total_revenue ?? 0),
            'total_cost' => (float)($stats->total_cost ?? 0),
            'total_profit' => (float)($stats->total_profit ?? 0),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    public function detailedReport(Request $request)
    {
        $startDate = $request->start_date
            ? \Carbon\Carbon::parse($request->start_date)->startOfDay()->toDateTimeString()
            : \Carbon\Carbon::now()->startOfMonth()->startOfDay()->toDateTimeString();
        $endDate = $request->end_date
            ? \Carbon\Carbon::parse($request->end_date)->endOfDay()->toDateTimeString()
            : \Carbon\Carbon::now()->endOfDay()->toDateTimeString();

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
