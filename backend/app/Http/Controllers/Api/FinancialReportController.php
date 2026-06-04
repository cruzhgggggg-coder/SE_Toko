<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialReport;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\DebtPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialReportController extends Controller
{
    public function index(Request $request)
    {
        $reports = FinancialReport::orderBy('report_date', 'desc')
            ->paginate($request->get('per_page', 15));
        
        return response()->json($reports);
    }

    public function generate(Request $request)
    {
        $date = $request->get('date', Carbon::today()->toDateString());
        
        $revenue = Transaction::whereDate('transaction_date', $date)->sum('total_amount');

        $costData = TransactionItem::whereHas('transaction', function ($query) use ($date) {
            $query->whereDate('transaction_date', $date);
        })->select(
            DB::raw('SUM(qty * cost_price) as total_cost'),
            DB::raw('SUM(subtotal) as total_revenue_items')
        )->first();

        $totalCost = (float)($costData->total_cost ?? 0);
        $totalRevenueItems = (float)($costData->total_revenue_items ?? 0);
        $profit = $totalRevenueItems - $totalCost;

        $count = Transaction::whereDate('transaction_date', $date)->count();

        $debtPayments = DebtPayment::whereDate('payment_date', $date)->sum('amount');

        $newDebt = Transaction::whereDate('transaction_date', $date)
            ->where('payment_method', 'debt')
            ->sum('total_amount');

        $expenses = 0;

        return response()->json([
            'report_date' => $date,
            'total_revenue' => (float)$revenue,
            'total_cost' => $totalCost,
            'total_profit' => (float)$profit,
            'total_transactions' => $count,
            'transaction_count' => $count,
            'debt_payments_received' => (float)$debtPayments,
            'new_debt_amount' => (float)$newDebt,
            'expense_amount' => (float)$expenses,
            'net_income' => ((float)$revenue + (float)$debtPayments) - (float)$expenses,
            'created_by_user' => $request->user()->name ?? 'system',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'report_date' => 'required|date|unique:financial_reports,report_date',
            'total_revenue' => 'required|numeric',
            'total_cost' => 'required|numeric',
            'total_profit' => 'required|numeric',
            'transaction_count' => 'required|integer',
            'new_debt_amount' => 'nullable|numeric',
            'debt_payments_received' => 'nullable|numeric',
            'expense_amount' => 'nullable|numeric',
            'net_income' => 'required|numeric',
            'created_by_user' => 'nullable|string',
        ]);

        $validated['new_debt_amount'] = $validated['new_debt_amount'] ?? 0;
        $validated['debt_payments_received'] = $validated['debt_payments_received'] ?? 0;
        $validated['expense_amount'] = $validated['expense_amount'] ?? 0;
        $validated['created_by_user'] = $validated['created_by_user'] ?? $request->user()?->name ?? 'system';

        $report = FinancialReport::create($validated);

        return response()->json([
            'message' => 'Laporan harian berhasil disimpan secara permanen.',
            'data' => $report
        ], 201);
    }

    public function show($id)
    {
        $report = FinancialReport::findOrFail($id);
        return response()->json($report);
    }
}
