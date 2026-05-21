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
        Schema::create('stock_batches', function (Blueprint $row) {
            $row->id();
            $row->foreignId('product_id')->constrained()->onDelete('cascade');
            $row->string('batch_number');
            $row->integer('qty');
            $row->integer('current_qty'); // Remaining qty for FIFO
            $row->decimal('buy_price', 15, 2);
            $row->decimal('sell_price', 15, 2);
            $row->date('expired_date')->nullable();
            $row->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_batches');
    }
};
