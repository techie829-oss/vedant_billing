<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->decimal('mrp', 15, 2)->nullable()->after('unit_price');
            $table->string('batch_number')->nullable()->after('mrp');
            $table->date('expiry_date')->nullable()->after('batch_number');
        });

        Schema::table('temp_products', function (Blueprint $table) {
            $table->decimal('mrp', 15, 2)->nullable()->after('price');
            $table->string('batch_number')->nullable()->after('mrp');
            $table->date('expiry_date')->nullable()->after('batch_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('temp_products', function (Blueprint $table) {
            $table->dropColumn(['mrp', 'batch_number', 'expiry_date']);
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['mrp', 'batch_number', 'expiry_date']);
        });
    }
};
