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
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('minimum_price', 12, 2);
            $table->decimal('commission_percent', 5, 2);
            $table->timestamps();

            $table->unique(['agent_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_product_terms');
    }
};
