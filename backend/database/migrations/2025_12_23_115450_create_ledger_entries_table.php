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
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained()->cascadeOnDelete();

            // Link to Header
            $table->foreignUuid('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();

            // Link to Account (Ledger)
            $table->foreignUuid('ledger_id')->constrained('ledgers')->restrictOnDelete();

            $table->string('type'); // debit, credit
            $table->decimal('amount', 15, 2); // Stored in decimal (always positive)

            $table->text('description')->nullable(); // Line item description

            $table->timestamps();

            // Index for fast account balance calculation
            $table->index(['ledger_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
