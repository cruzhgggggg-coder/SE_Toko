<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Customer;
use App\Models\Debt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats()
    {
        $today = now()->startOfDay();
        $endOfToday = now()->endOfDay();

        // 1. Sales & Profit Stats
        $salesToday = Transaction::whereBetween('transaction_date', [$today, $endOfToday])->sum('total_amount');
        $transactionCountToday = Transaction::whereBetween('transaction_date', [$today, $endOfToday])->count();
        
        // Calculate Profit: Sum(total_amount) - Sum(cost_price * qty)
        $costToday = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->whereBetween('transactions.transaction_date', [$today, $endOfToday])
            ->sum(DB::raw('transaction_items.cost_price * transaction_items.qty'));
            
        $profitToday = $salesToday - $costToday;
        $netMargin = $salesToday > 0 ? ($profitToday / $salesToday) * 100 : 0;

        $lowStockCount = Product::with(['stockBatches'])->get()
            ->filter(function($product) {
                return $product->stockBatches->sum('current_qty') <= $product->min_stock;
            })->count();
        
        // 3. Financial Stats
        $totalPendingDebt = Debt::where('status', 'unpaid')->sum('remaining_amount');
        
        // 4. Monthly Sales & Profit Chart Data (Last 8 months as per design mockup)
        $chartData = [];
        for ($i = 7; $i >= 0; $i--) {
            $targetMonth = now()->subMonths($i);
            $monthNum = $targetMonth->month;
            $yearNum = $targetMonth->year;
            
            $mSales = Transaction::whereMonth('transaction_date', $monthNum)
                ->whereYear('transaction_date', $yearNum)
                ->sum('total_amount');
                
            $mCost = DB::table('transaction_items')
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->whereMonth('transactions.transaction_date', $monthNum)
                ->whereYear('transactions.transaction_date', $yearNum)
                ->sum(DB::raw('transaction_items.cost_price * transaction_items.qty'));
            
            $chartData[] = [
                'day' => strtoupper($targetMonth->shortMonthName),
                'amount' => (float)$mSales,
                'profit' => (float)($mSales - $mCost)
            ];
        }

        return response()->json([
            'sales_today' => (float)$salesToday,
            'profit_today' => (float)$profitToday,
            'net_margin' => (float)$netMargin,
            'transaction_count_today' => $transactionCountToday,
            'low_stock_count' => $lowStockCount,
            'total_pending_debt' => (float)$totalPendingDebt,
            'chart_data' => $chartData,
            'recent_transactions' => Transaction::with('customer')->latest()->take(5)->get()
        ]);
    }
}
