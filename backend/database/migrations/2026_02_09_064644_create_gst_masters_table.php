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
        Schema::create('gst_masters', function (Blueprint $table) {
            $table->id();
            $table->string('gstin')->unique();
            $table->string('legal_name')->nullable();
            $table->string('trade_name')->nullable();
            $table->text('address')->nullable(); // Can store formatted address or JSON
            $table->string('status')->nullable(); // Active, Cancelled, etc.
            $table->json('meta')->nullable(); // Full API response
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gst_masters');
    }
};
