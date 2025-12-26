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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();

            $table->string('name');
            $table->string('sku')->nullable();
            $table->enum('type', ['goods', 'service'])->default('goods');

            $table->decimal('sale_price', 15, 2)->default(0);
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->decimal('current_stock', 15, 2)->default(0); // Denormalized cache

            $table->string('unit')->default('pcs');
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'sku']);
            $table->index(['business_id', 'type']);
        });

        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();

            $table->enum('type', ['purchase', 'sale', 'adjustment', 'return']);
            $table->decimal('quantity', 15, 2); // +/- values
            $table->string('notes')->nullable();
            $table->uuid('reference_id')->nullable(); // Polymorphic could be better but simple ID for now

            $table->timestamps();

            $table->index(['business_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('products');
    }
};
