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
        Schema::create('customers', function (Blueprint $row) {
            $row->id();
            $row->string('name');
            $row->string('phone')->nullable();
            $row->text('address')->nullable();
            $row->decimal('debt_limit', 15, 2)->default(0);
            $row->decimal('current_debt', 15, 2)->default(0);
            $row->boolean('is_active')->default(true);
            $row->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
