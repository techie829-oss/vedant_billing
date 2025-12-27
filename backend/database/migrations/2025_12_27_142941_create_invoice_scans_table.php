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
        Schema::create('invoice_scans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('business_id');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');

            // Image and metadata
            $table->string('image_path');
            $table->string('vendor_name')->nullable();
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();

            // OCR/LLM data
            $table->text('raw_ocr_text')->nullable();
            $table->json('llm_response')->nullable();

            // Status tracking
            $table->enum('status', ['success', 'failed', 'pending'])->default('pending');
            $table->text('error_message')->nullable();
            $table->integer('products_count')->default(0);

            $table->timestamps();

            $table->index(['business_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_scans');
    }
};
