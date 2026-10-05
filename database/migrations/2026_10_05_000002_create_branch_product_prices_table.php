<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 12, 2);
            $table->timestamps();

            $table->unique(['branch_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_product_prices');
    }
};
