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
        Schema::create('debts', function (Blueprint $row) {
            $row->id();
            $row->foreignId('customer_id')->constrained();
            $row->foreignId('transaction_id')->constrained();
            $row->decimal('amount', 15, 2);
            $row->decimal('remaining_amount', 15, 2);
            $row->date('due_date')->nullable();
            $row->string('status')->default('unpaid'); // unpaid, partial, paid
            $row->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
