<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'supplier', 'stockBatches' => function($query) {
            $query->where('current_qty', '>', 0)->orderBy('expired_date', 'asc');
        }])->get()->map(function($product) {
            $totalStock = $product->stockBatches->sum('current_qty');
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'category' => $product->category?->name ?? $product->category, // fallback to old text field if needed
                'category_id' => $product->category_id,
                'supplier_id' => $product->supplier_id,
                'supplier_name' => $product->supplier?->name,
                'unit' => $product->unit,
                'min_stock' => $product->min_stock,
                'total_stock' => $totalStock,
                'is_low_stock' => $totalStock <= $product->min_stock,
                'batches' => $product->stockBatches
            ];
        });

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255|unique:products,sku',
            'category' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit' => 'required|string|max:50',
            'min_stock' => 'required|integer|min:0',
            'base_buy_price' => 'nullable|numeric|min:0',
            'base_sell_price' => 'nullable|numeric|min:0',
            'initial_stock' => 'nullable|integer|min:0',
        ]);

        // Auto-generate SKU if not provided
        if (empty($validated['sku'])) {
            $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $validated['name']), 0, 3)) ?: 'PRD';
            do {
                $validated['sku'] = $prefix . '-' . strtoupper(substr(uniqid(), -5));
            } while (Product::where('sku', $validated['sku'])->exists());
        }

        if (!empty($validated['category_id'])) {
            $cat = \App\Models\Category::find($validated['category_id']);
            if ($cat) {
                $validated['category'] = $cat->name;
            }
        } elseif (!empty($validated['category'])) {
            $cat = \App\Models\Category::firstOrCreate(['name' => $validated['category']]);
            $validated['category_id'] = $cat->id;
        } else {
            $validated['category'] = 'Umum';
            $cat = \App\Models\Category::firstOrCreate(['name' => 'Umum']);
            $validated['category_id'] = $cat->id;
        }

        return DB::transaction(function () use ($validated, $request) {
            $product = Product::create($validated);

            $initialStock = $request->input('initial_stock', 0);
            if ($initialStock > 0) {
                $batch = $product->stockBatches()->create([
                    'batch_number' => 'INIT-' . time(),
                    'qty' => $initialStock,
                    'current_qty' => $initialStock,
                    'buy_price' => $request->input('base_buy_price', 0),
                    'sell_price' => $request->input('base_sell_price', 0),
                ]);

                $product->stockLogs()->create([
                    'stock_batch_id' => $batch->id,
                    'type' => 'RESTOCK',
                    'quantity' => $initialStock,
                    'user_id' => $request->user()?->id ?? 1,
                    'notes' => "Initial stock batch",
                ]);
            }

            $product->updateLowStockStatus();

            return response()->json($product, 201);
        });
    }

    public function addStock(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        if ($request->has('expired_date') && $request->expired_date === '') {
            $request->merge(['expired_date' => null]);
        }
        if ($request->has('rack_location') && $request->rack_location === '') {
            $request->merge(['rack_location' => null]);
        }

        $validated = $request->validate([
            'batch_number' => 'required|string|max:255',
            'qty' => 'required|integer|min:1',
            'buy_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
            'expired_date' => 'nullable|date',
            'rack_location' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($product, $validated, $request) {
            $batch = $product->stockBatches()->create([
                'batch_number' => $validated['batch_number'],
                'qty' => $validated['qty'],
                'current_qty' => $validated['qty'],
                'buy_price' => $validated['buy_price'],
                'sell_price' => $validated['sell_price'],
                'expired_date' => $request->input('expired_date'),
                'rack_location' => $request->input('rack_location'),
            ]);

            $product->stockLogs()->create([
                'stock_batch_id' => $batch->id,
                'type' => 'RESTOCK',
                'quantity' => $validated['qty'],
                'user_id' => $request->user()?->id ?? 1,
                'notes' => "Restock batch: {$validated['batch_number']}",
            ]);

            $product->updateLowStockStatus();

            return response()->json($batch, 201);
        });
    }

    public function discardBatch(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'stock_batch_id' => 'required|exists:stock_batches,id',
        ]);

        return DB::transaction(function () use ($product, $validated, $request) {
            $batch = $product->stockBatches()->findOrFail($validated['stock_batch_id']);

            $discardQty = $batch->current_qty;
            if ($discardQty > 0) {
                $batch->update(['current_qty' => 0]);

                // Create stock log for expired discard
                $product->stockLogs()->create([
                    'stock_batch_id' => $batch->id,
                    'type' => 'EXPIRED',
                    'quantity' => -$discardQty,
                    'user_id' => $request->user()?->id ?? 1,
                    'notes' => "Buang stok kadaluarsa (Batch: {$batch->batch_number})",
                ]);

                $product->updateLowStockStatus();
            }

            return response()->json(['message' => 'Stok kadaluarsa berhasil dibuang.'], 200);
        });
    }

    public function show(string $id)
    {
        $product = Product::with(['category', 'supplier', 'stockBatches' => function($query) {
            $query->where('current_qty', '>', 0)->orderBy('expired_date', 'asc');
        }])->findOrFail($id);

        $totalStock = $product->stockBatches->sum('current_qty');

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'category_id' => $product->category_id,
            'category' => $product->category?->name ?? $product->category,
            'supplier_id' => $product->supplier_id,
            'supplier_name' => $product->supplier?->name,
            'unit' => $product->unit,
            'min_stock' => $product->min_stock,
            'total_stock' => $totalStock,
            'batches' => $product->stockBatches
        ]);
    }

    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:products,sku,' . $id,
            'category' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit' => 'required|string|max:50',
            'min_stock' => 'required|integer|min:0',
            'base_buy_price' => 'nullable|numeric|min:0',
            'base_sell_price' => 'nullable|numeric|min:0',
        ]);

        if (!empty($validated['category_id'])) {
            $cat = \App\Models\Category::find($validated['category_id']);
            if ($cat) {
                $validated['category'] = $cat->name;
            }
        } elseif (!empty($validated['category'])) {
            $cat = \App\Models\Category::firstOrCreate(['name' => $validated['category']]);
            $validated['category_id'] = $cat->id;
        }

        $product->update($validated);
        $product->updateLowStockStatus();

        return response()->json($product);
    }

    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        
        // Safety check: Don't delete if there's stock or transaction history
        if ($product->stockBatches()->sum('current_qty') > 0) {
            return response()->json(['message' => 'Produk tidak dapat dihapus karena masih ada stok.'], 400);
        }

        $product->delete();
        return response()->json(['message' => 'Produk berhasil dihapus.']);
    }

    public function stockHistory(string $id)
    {
        $product = Product::findOrFail($id);
        $logs = $product->stockLogs()
            ->with(['user', 'stockBatch'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($logs);
    }
}
