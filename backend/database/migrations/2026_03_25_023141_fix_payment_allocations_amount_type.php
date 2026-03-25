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
            // PostgreSQL specific fix: cast amount to numeric/decimal explicitly
            DB::statement("ALTER TABLE payment_allocations ALTER COLUMN amount TYPE numeric(15,2) USING amount::numeric(15,2)");
        } else {
            Schema::table('payment_allocations', function (Blueprint $table) {
                $table->decimal('amount', 15, 2)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No easy way to revert if we don't know the exact previous type, 
        // but decimal(15,2) is the correct type.
    }
};
