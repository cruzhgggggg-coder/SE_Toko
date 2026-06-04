<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('financial_reports', function (Blueprint $table) {
            $table->decimal('expense_amount', 15, 2)->default(0)->after('debt_payments_received');
            $table->decimal('net_income', 15, 2)->default(0)->after('expense_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_reports', function (Blueprint $table) {
            $table->dropColumn(['expense_amount', 'net_income']);
        });
    }
};
