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
        Schema::create('products', function (Blueprint $row) {
            $row->id();
            $row->string('name');
            $row->string('sku')->unique();
            $row->string('category');
            $row->string('unit')->default('pcs');
            $row->integer('min_stock')->default(10);
            $row->boolean('is_low_stock')->default(false);
            $row->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
