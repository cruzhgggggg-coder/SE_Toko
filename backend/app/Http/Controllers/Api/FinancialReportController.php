<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialReport;
use App\Models\Transaction;
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
        
        $revenue = Transaction::whereDate('created_at', $date)->sum('total_amount');
        $profit = Transaction::whereDate('created_at', $date)->sum('profit');
        $count = Transaction::whereDate('created_at', $date)->count();
        $debtPayments = DebtPayment::whereDate('payment_date', $date)->sum('amount');
        
        $newDebt = Transaction::whereDate('created_at', $date)
            ->where('payment_method', 'debt')
            ->sum(DB::raw('total_amount - paid_amount'));

        // For now, expenses are just a placeholder or could be linked to a future Expenses table
        $expenses = 0; 

        return response()->json([
            'report_date' => $date,
            'total_revenue' => $revenue,
            'total_profit' => $profit,
            'total_transactions' => $count,
            'total_debt_payments' => $debtPayments,
            'new_debt_amount' => $newDebt,
            'expense_amount' => $expenses,
            'net_income' => ($revenue + $debtPayments) - $expenses
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'report_date' => 'required|date|unique:financial_reports,report_date',
            'total_revenue' => 'required|numeric',
            'total_profit' => 'required|numeric',
            'total_transactions' => 'required|integer',
            'total_debt_payments' => 'nullable|numeric',
            'new_debt_amount' => 'nullable|numeric',
            'expense_amount' => 'nullable|numeric',
            'net_income' => 'required|numeric',
            'metadata' => 'nullable|array'
        ]);

        $report = FinancialReport::create($validated);

        return response()->json([
            'message' => 'Financial report saved successfully',
            'data' => $report
        ], 201);
    }

    public function show($id)
    {
        $report = FinancialReport::findOrFail($id);
        return response()->json($report);
    }
}
