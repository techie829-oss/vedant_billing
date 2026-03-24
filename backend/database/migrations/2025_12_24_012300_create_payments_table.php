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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained()->cascadeOnDelete();

            // Placeholder: Will be constrained to 'customers' table in Phase 5
            $table->uuid('customer_id')->nullable()->index();

            $table->decimal('amount', 15, 2); // Stored as decimal (Rupees)
            $table->string('currency', 3)->default('USD');

            $table->date('date')->index();
            $table->string('method')->index(); // card, bank_transfer, cash, check, other
            $table->string('reference')->nullable(); // e.g. check number, stripe id

            $table->string('status')->default('completed')->index(); // pending, completed, voided, refunded

            $table->text('notes')->nullable();
            $table->jsonb('meta')->nullable();

            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
