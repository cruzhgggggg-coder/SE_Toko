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
        'total_profit',
        'total_transactions',
        'total_debt_payments',
        'new_debt_amount',
        'expense_amount',
        'net_income',
        'metadata'
    ];

    protected $casts = [
        'report_date' => 'date',
        'metadata' => 'array',
        'total_revenue' => 'decimal:2',
        'total_profit' => 'decimal:2',
        'total_debt_payments' => 'decimal:2',
        'new_debt_amount' => 'decimal:2',
        'expense_amount' => 'decimal:2',
        'net_income' => 'decimal:2',
    ];
}
