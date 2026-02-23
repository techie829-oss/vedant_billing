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
            $table->decimal('cess_rate', 5, 2)->default(0)->after('tax_rate');
        });

        Schema::table('temp_products', function (Blueprint $table) {
            $table->decimal('cess_rate', 5, 2)->default(0)->after('tax_rate');
            $table->decimal('cess_amount', 15, 2)->default(0)->after('cess_rate');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->decimal('cess_rate', 5, 2)->default(0)->after('tax_amount');
            $table->decimal('cess_amount', 15, 2)->default(0)->after('cess_rate');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('cess_total', 15, 2)->default(0)->after('tax_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('cess_total');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['cess_rate', 'cess_amount']);
        });

        Schema::table('temp_products', function (Blueprint $table) {
            $table->dropColumn(['cess_rate', 'cess_amount']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('cess_rate');
        });
    }
};
