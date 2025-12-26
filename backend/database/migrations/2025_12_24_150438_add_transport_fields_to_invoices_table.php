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
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('challan_no')->nullable()->after('notes');
            $table->string('eway_bill_no')->nullable()->after('challan_no');
            $table->string('vehicle_no')->nullable()->after('eway_bill_no');
            $table->string('po_number')->nullable()->after('vehicle_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['challan_no', 'eway_bill_no', 'vehicle_no', 'po_number']);
        });
    }
};
