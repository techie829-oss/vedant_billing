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
        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained()->cascadeOnDelete();

            $table->foreignUuid('payment_id')->constrained('payments')->cascadeOnDelete();

            // Placeholder: Will be constrained to 'invoices' table in Phase 5
            $table->uuid('invoice_id')->nullable()->index();

            $table->decimal('amount', 15, 2); // Amount applied to this invoice (Rupees)

            $table->timestamps();

            // Prevent allocating the same payment to the same invoice multiple times (optional, but good practice)
            // Actually, you might allocate multiple times if you unallocate and reallocate. 
            // Better to index payment_id for lookup.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
    }
};
