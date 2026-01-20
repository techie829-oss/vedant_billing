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
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->decimal('unit_price', 15, 2)->nullable()->after('quantity'); // Buy Price / Cost Price
            $table->foreignUuid('party_id')->nullable()->after('product_id')->constrained('parties')->nullOnDelete(); // Supplier or Customer
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropColumn(['unit_price', 'party_id']);
        });
    }
};
