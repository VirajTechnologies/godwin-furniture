<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_product_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('commission_percent', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['agent_id', 'product_variant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_product_terms');
    }
};
