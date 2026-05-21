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
        Schema::create('transaction_items', function (Blueprint $row) {
            $row->id();
            $row->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $row->foreignId('product_id')->constrained();
            $row->integer('qty');
            $row->decimal('price', 15, 2);
            $row->decimal('subtotal', 15, 2);
            $row->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
