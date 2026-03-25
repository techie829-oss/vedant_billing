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
            // Update STATUS Enum/Check
            DB::statement("ALTER TABLE invoices DROP CONSTRAINT IF EXISTS invoices_status_check");
            DB::statement("ALTER TABLE invoices ADD CONSTRAINT invoices_status_check CHECK (status IN ('draft', 'sent', 'paid', 'partial', 'overdue', 'void', 'cancelled', 'returned'))");
            
            // Update TYPE Enum/Check
            DB::statement("ALTER TABLE invoices DROP CONSTRAINT IF EXISTS invoices_type_check");
            DB::statement("ALTER TABLE invoices ADD CONSTRAINT invoices_type_check CHECK (type IN ('invoice', 'tax_invoice', 'bill_of_supply', 'purchase_invoice', 'proforma_invoice', 'delivery_challan', 'credit_note', 'debit_note', 'quote', 'estimate'))");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft', 'sent', 'paid', 'partial', 'overdue', 'void', 'cancelled', 'returned') DEFAULT 'draft'");
            DB::statement("ALTER TABLE invoices MODIFY COLUMN type ENUM('invoice', 'tax_invoice', 'bill_of_supply', 'purchase_invoice', 'proforma_invoice', 'delivery_challan', 'credit_note', 'debit_note', 'quote', 'estimate') DEFAULT 'invoice'");
        } else {
            // SQLite or others
            Schema::table('invoices', function (Blueprint $table) {
                $table->string('status')->default('draft')->change();
                $table->string('type')->default('invoice')->change();
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
            
            DB::statement("ALTER TABLE invoices DROP CONSTRAINT IF EXISTS invoices_type_check");
            DB::statement("ALTER TABLE invoices ADD CONSTRAINT invoices_type_check CHECK (type IN ('invoice', 'tax_invoice', 'bill_of_supply', 'purchase_invoice', 'proforma_invoice', 'delivery_challan', 'credit_note', 'debit_note', 'quote', 'estimate'))");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft', 'sent', 'paid', 'overdue', 'void', 'cancelled') DEFAULT 'draft'");
            DB::statement("ALTER TABLE invoices MODIFY COLUMN type ENUM('invoice', 'tax_invoice', 'bill_of_supply', 'purchase_invoice', 'proforma_invoice', 'delivery_challan', 'credit_note', 'debit_note', 'quote', 'estimate') DEFAULT 'invoice'");
        }
    }
};
