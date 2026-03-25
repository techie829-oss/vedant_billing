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
        Schema::table('products', function (Blueprint $table) {
            // Renaming existing unit to base_unit might be a bit breaking, 
            // but the request implies we have a unit and then pieces per unit.
            // Let's keep 'unit' as the primary (base) unit.
            $table->string('secondary_unit')->nullable()->after('unit');
            $table->decimal('conversion_factor', 15, 2)->default(1.00)->after('secondary_unit');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('unit')->nullable()->after('quantity');
            $table->decimal('conversion_factor', 15, 2)->default(1.00)->after('unit');
        });

        // Also add unit to inventory_transactions if it helps for logging, 
        // though stock is always tracked in base units.
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->string('unit')->nullable()->after('quantity');
            $table->decimal('conversion_factor', 15, 2)->default(1.00)->after('unit');
            $table->string('reference_type')->nullable()->after('reference_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['secondary_unit', 'conversion_factor']);
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['unit', 'conversion_factor']);
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropColumn(['unit', 'conversion_factor', 'reference_type']);
        });
    }
};
