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
        Schema::create('temp_products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('business_id');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');

            $table->uuid('scan_reference_id');
            $table->foreign('scan_reference_id')->references('id')->on('invoice_scans')->onDelete('cascade');

            // Extracted product data
            $table->string('name');
            $table->string('sku')->nullable();
            $table->decimal('price', 15, 2);
            $table->decimal('quantity', 10, 2);
            $table->string('unit')->nullable(); // kg, pcs, box, etc.
            $table->text('description')->nullable();
            $table->string('hsn_code')->nullable();
            $table->decimal('tax_rate', 5, 2)->nullable();

            // Matching and status
            $table->uuid('matched_product_id')->nullable();
            $table->foreign('matched_product_id')->references('id')->on('products')->onDelete('set null');
            $table->enum('status', ['pending', 'matched', 'added', 'rejected'])->default('pending');
            $table->decimal('confidence_score', 3, 2)->nullable(); // 0.00 to 1.00

            $table->timestamps();

            $table->index(['business_id', 'scan_reference_id']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_products');
    }
};
