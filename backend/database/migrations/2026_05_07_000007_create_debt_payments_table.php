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
        Schema::create('debt_payments', function (Blueprint $row) {
            $row->id();
            $row->foreignId('debt_id')->constrained()->onDelete('cascade');
            $row->decimal('amount', 15, 2);
            $row->timestamp('payment_date')->useCurrent();
            $row->text('note')->nullable();
            $row->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debt_payments');
    }
};
