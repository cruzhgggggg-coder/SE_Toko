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

        // 1. Low Stock Notifications — optimized with database-level aggregation
        $lowStockProducts = DB::select('
            SELECT p.id, p.name, p.unit, p.min_stock,
                   COALESCE(SUM(sb.current_qty), 0) as total_stock
            FROM products p
            LEFT JOIN stock_batches sb ON sb.product_id = p.id AND sb.current_qty > 0
            GROUP BY p.id, p.name, p.unit, p.min_stock
            HAVING total_stock <= CASE
                WHEN p.min_stock > 0 THEN p.min_stock
                ELSE ?
            END
        ', [$globalThreshold]);

        foreach ($lowStockProducts as $product) {
            $totalStock = (int) $product->total_stock;
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
            ->select(
                'products.id as product_id',
                'products.name',
                'stock_batches.id as stock_batch_id',
                'stock_batches.expired_date',
                'stock_batches.batch_number'
            )
            ->get();

        foreach ($nearExpiryBatches as $batch) {
            $daysLeft = max(0, (int) now()->diffInDays($batch->expired_date, false));
            $notifications[] = [
                'type' => 'EXPIRY',
                'title' => 'Produk Hampir Kedaluwarsa',
                'message' => "Produk {$batch->name} (Batch: {$batch->batch_number}) akan kedaluwarsa dalam {$daysLeft} hari.",
                'product_id' => $batch->product_id,
                'stock_batch_id' => $batch->stock_batch_id,
                'batch_number' => $batch->batch_number,
                'severity' => $daysLeft <= 7 ? 'high' : 'medium',
            ];
        }

        // 3. Overdue Debts — with limit to prevent excessive notifications
        $overdueDebts = Debt::with('customer')
            ->where('status', 'unpaid')
            ->where('due_date', '<', now())
            ->limit(50)
            ->get();

        foreach ($overdueDebts as $debt) {
            if ($debt->customer) {
                $notifications[] = [
                    'type' => 'OVERDUE_DEBT',
                    'title' => 'Hutang Lewat Jatuh Tempo',
                    'message' => "Hutang pelanggan {$debt->customer->name} sebesar " .
                                 number_format($debt->remaining_amount) . " telah melewati jatuh tempo.",
                    'customer_id' => $debt->customer_id,
                    'severity' => 'high',
                ];
            }
        }

        return response()->json($notifications);
    }
}
