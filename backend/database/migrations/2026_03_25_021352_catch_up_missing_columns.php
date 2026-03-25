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
        $tables = ['ledgers', 'journal_entries', 'payments', 'invoice_scans', 'quick_notes'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'created_by')) {
                    $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn($tableName, 'updated_by')) {
                    $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
                }
                
                if ($tableName === 'quick_notes') {
                    if (!Schema::hasColumn($tableName, 'deleted_at')) {
                        $table->softDeletes();
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['ledgers', 'journal_entries', 'payments', 'invoice_scans', 'quick_notes'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $columns = ['created_by', 'updated_by'];
                if ($tableName === 'quick_notes') {
                    $columns[] = 'deleted_at';
                }
                
                foreach ($columns as $col) {
                    if (Schema::hasColumn($tableName, $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
