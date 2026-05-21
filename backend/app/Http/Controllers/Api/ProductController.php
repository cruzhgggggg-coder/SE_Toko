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
            'sku' => 'required|string|max:255|unique:products,sku',
            'category' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit' => 'required|string|max:50',
            'min_stock' => 'required|integer|min:0',
            'base_buy_price' => 'nullable|numeric|min:0',
            'base_sell_price' => 'nullable|numeric|min:0',
        ]);

        $product = Product::create($validated);

        return response()->json($product, 201);
    }

    public function addStock(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

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
                'expired_date' => $validated['expired_date'],
                'rack_location' => $validated['rack_location'] ?? null,
            ]);

            $product->stockLogs()->create([
                'stock_batch_id' => $batch->id,
                'type' => 'RESTOCK',
                'quantity' => $validated['qty'],
                'user_id' => $request->user()->id,
                'notes' => "Restock batch: {$validated['batch_number']}",
            ]);

            return response()->json($batch, 201);
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

        $product->update($validated);

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
