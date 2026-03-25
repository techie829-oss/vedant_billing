<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL handles enums as check constraints in Laravel migrations
            // We need to drop the old check constraint and add a new one.
            // The constraint name is usually 'invoices_status_check'
            
            DB::statement("ALTER TABLE invoices DROP CONSTRAINT IF EXISTS invoices_status_check");
            
            // Add new check constraint with more statuses
            DB::statement("ALTER TABLE invoices ADD CONSTRAINT invoices_status_check CHECK (status IN ('draft', 'sent', 'paid', 'partial', 'overdue', 'void', 'cancelled', 'returned'))");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft', 'sent', 'paid', 'partial', 'overdue', 'void', 'cancelled', 'returned') DEFAULT 'draft'");
        } else {
            // SQLite or others - might need to recreate table or ignore if it's just a string
            Schema::table('invoices', function (Blueprint $table) {
                $table->string('status')->default('draft')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE invoices DROP CONSTRAINT IF EXISTS invoices_status_check");
            DB::statement("ALTER TABLE invoices ADD CONSTRAINT invoices_status_check CHECK (status IN ('draft', 'sent', 'paid', 'overdue', 'void', 'cancelled'))");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft', 'sent', 'paid', 'overdue', 'void', 'cancelled') DEFAULT 'draft'");
        }
    }
};
