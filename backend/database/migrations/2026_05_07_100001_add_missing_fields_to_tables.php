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
        Schema::table('stock_batches', function (Blueprint $table) {
            $table->string('rack_location')->nullable()->after('expired_date');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_number')->nullable()->after('role');
            $table->timestamp('last_login')->nullable()->after('phone_number');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('base_buy_price', 15, 2)->default(0)->after('unit');
            $table->decimal('base_sell_price', 15, 2)->default(0)->after('base_buy_price');
            $table->string('image')->nullable()->after('base_sell_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_batches', function (Blueprint $table) {
            $table->dropColumn('rack_location');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone_number', 'last_login']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['base_buy_price', 'base_sell_price']);
        });
    }
};
