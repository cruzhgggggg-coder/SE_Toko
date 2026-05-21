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
        Schema::create('financial_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date')->unique();
            $table->decimal('total_revenue', 15, 2);
            $table->decimal('total_cost', 15, 2);
            $table->decimal('total_profit', 15, 2);
            $table->integer('transaction_count');
            $table->decimal('new_debt_amount', 15, 2);
            $table->decimal('debt_payments_received', 15, 2);
            $table->string('created_by_user');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_reports');
    }
};
