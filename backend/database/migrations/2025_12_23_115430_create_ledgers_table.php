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
        Schema::create('ledgers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('code')->nullable()->index(); // e.g. '1010'
            $table->string('type')->index(); // asset, liability, equity, revenue, expense
            $table->text('description')->nullable();

            $table->boolean('is_system')->default(false); // If true, cannot be deleted
            $table->jsonb('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Unique name per business, code per business
            $table->unique(['business_id', 'name']);
            $table->unique(['business_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledgers');
    }
};
