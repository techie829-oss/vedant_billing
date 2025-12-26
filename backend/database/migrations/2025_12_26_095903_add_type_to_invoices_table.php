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
            $table->string('type')->default('invoice')->after('id')->index(); // 'invoice', 'credit_note'
            $table->uuid('parent_id')->nullable()->after('type'); // Link to original invoice
            $table->text('reason')->nullable()->after('terms'); // Reason for return
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['type', 'parent_id', 'reason']);
        });
    }
};
