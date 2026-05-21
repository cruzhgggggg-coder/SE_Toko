<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransactionReturn;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Models\StockBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionReturnController extends Controller
{
    public function index()
    {
        return response()->json(TransactionReturn::with(['transaction', 'transactionItem.product', 'user'])->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_item_id' => 'required|exists:transaction_items,id',
            'qty' => 'required|integer|min:1',
            'reason' => 'required|string',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            $transactionItem = TransactionItem::findOrFail($validated['transaction_item_id']);
            
            // Validate returned qty doesn't exceed bought qty
            $alreadyReturned = TransactionReturn::where('transaction_item_id', $transactionItem->id)
                ->where('status', '!=', 'rejected')
                ->sum('qty');
            
            if ($validated['qty'] > ($transactionItem->qty - $alreadyReturned)) {
                throw ValidationException::withMessages([
                    'qty' => ['Kuantitas retur melebihi yang bisa diretur.']
                ]);
            }

            // Create Return record
            $returnRecord = TransactionReturn::create([
                'transaction_id' => $transactionItem->transaction_id,
                'transaction_item_id' => $transactionItem->id,
                'user_id' => $request->user()->id,
                'qty' => $validated['qty'],
                'reason' => $validated['reason'],
                'status' => 'approved', // Auto approve for simple implementation
            ]);

            // Add stock back
            $product = Product::findOrFail($transactionItem->product_id);
            // Put it into a new stock batch or adjust the latest one. Let's create a new batch for returned items.
            $batch = StockBatch::create([
                'product_id' => $product->id,
                'batch_number' => 'RET-' . now()->format('Ymd') . '-' . $returnRecord->id,
                'buy_price' => $transactionItem->cost_price,
                'initial_qty' => $validated['qty'],
                'current_qty' => $validated['qty'],
                'expired_date' => now()->addMonths(6), // Assumption for returned goods
                'status' => 'available',
                'supplier_id' => null,
            ]);

            // Create log
            $product->stockLogs()->create([
                'stock_batch_id' => $batch->id,
                'type' => 'RETURN',
                'quantity' => $validated['qty'],
                'reference_id' => (string)$returnRecord->id,
                'user_id' => $request->user()->id,
                'notes' => "Barang retur dari transaksi #" . $transactionItem->transaction_id,
            ]);

            return response()->json($returnRecord->load(['transactionItem.product']), 201);
        });
    }
}
