<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Debt;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = [];

        // Get global low stock threshold from settings
        $globalThreshold = Setting::where('key', 'low_stock_threshold')->value('value');
        $globalThreshold = $globalThreshold ? (int) $globalThreshold : 10;

        // 1. Low Stock Notifications
        $lowStockProducts = Product::with(['stockBatches'])->get()
            ->filter(function($product) use ($globalThreshold) {
                $threshold = $product->min_stock > 0 ? $product->min_stock : $globalThreshold;
                return $product->stockBatches->sum('current_qty') <= $threshold;
            });

        foreach ($lowStockProducts as $product) {
            $totalStock = $product->stockBatches->sum('current_qty');
            $notifications[] = [
                'type' => 'LOW_STOCK',
                'title' => 'Stok Menipis',
                'message' => "Stok {$product->name} sisa {$totalStock} {$product->unit}.",
                'product_id' => $product->id,
                'severity' => $totalStock == 0 ? 'high' : 'medium',
            ];
        }

        // 2. Near Expiry Notifications (within 30 days)
        $nearExpiryBatches = DB::table('stock_batches')
            ->join('products', 'stock_batches.product_id', '=', 'products.id')
            ->where('stock_batches.current_qty', '>', 0)
            ->whereNotNull('stock_batches.expired_date')
            ->where('stock_batches.expired_date', '<=', now()->addDays(30))
            ->select('products.name', 'stock_batches.expired_date', 'stock_batches.batch_number')
            ->get();

        foreach ($nearExpiryBatches as $batch) {
            $daysLeft = now()->diffInDays($batch->expired_date, false);
            $notifications[] = [
                'type' => 'EXPIRY',
                'title' => 'Produk Hampir Kedaluwarsa',
                'message' => "Produk {$batch->name} (Batch: {$batch->batch_number}) akan kedaluwarsa dalam {$daysLeft} hari.",
                'severity' => $daysLeft <= 7 ? 'high' : 'medium',
            ];
        }

        // 3. Overdue Debts
        $overdueDebts = Debt::with('customer')
            ->where('status', 'unpaid')
            ->where('due_date', '<', now())
            ->get();

        foreach ($overdueDebts as $debt) {
            $notifications[] = [
                'type' => 'OVERDUE_DEBT',
                'title' => 'Hutang Lewat Jatuh Tempo',
                'message' => "Hutang pelanggan {$debt->customer->name} sebesar " . number_format($debt->remaining_amount) . " telah melewati jatuh tempo.",
                'customer_id' => $debt->customer_id,
                'severity' => 'high',
            ];
        }

        return response()->json($notifications);
    }
}
