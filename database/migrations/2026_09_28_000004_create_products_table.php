<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('code', 30)->unique();
            $table->string('slug', 160)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('project_category', 60)->nullable();
            $table->string('room', 80)->nullable();
            $table->string('product_category', 100)->nullable();
            $table->string('design_type', 100)->nullable();
            $table->string('family', 20)->nullable();
            $table->decimal('width_cm', 8, 2)->nullable();
            $table->decimal('depth_cm', 8, 2)->nullable();
            $table->decimal('height_cm', 8, 2)->nullable();
            $table->decimal('weight_kg', 8, 2)->nullable();
            $table->json('specs')->nullable();
            $table->string('material', 100)->nullable();
            $table->string('unit', 30)->default('Piece');
            $table->decimal('base_price', 12, 2)->default(0);
            $table->boolean('is_online')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku', 60)->unique();
            $table->string('colour', 60);
            $table->string('colour_hex', 9)->nullable();
            $table->string('finish', 60);
            $table->string('upholstery', 60)->default('');
            $table->decimal('price', 12, 2);
            $table->decimal('compare_at_price', 12, 2)->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['product_id', 'colour', 'finish', 'upholstery']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
    }
};
