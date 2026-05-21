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
        Schema::create('transactions', function (Blueprint $row) {
            $row->id();
            $row->foreignId('user_id')->constrained(); // The cashier/owner
            $row->foreignId('customer_id')->nullable()->constrained();
            $row->decimal('total_amount', 15, 2);
            $row->string('payment_method'); // cash, transfer, debt
            $row->string('status')->default('completed');
            $row->timestamp('transaction_date')->useCurrent();
            $row->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
