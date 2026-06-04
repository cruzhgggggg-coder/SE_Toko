<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_date',
        'total_revenue',
        'total_cost',
        'total_profit',
        'transaction_count',
        'new_debt_amount',
        'debt_payments_received',
        'expense_amount',
        'net_income',
        'created_by_user',
    ];

    protected $casts = [
        'report_date' => 'date',
        'total_revenue' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'total_profit' => 'decimal:2',
        'new_debt_amount' => 'decimal:2',
        'debt_payments_received' => 'decimal:2',
        'expense_amount' => 'decimal:2',
        'net_income' => 'decimal:2',
    ];
}
