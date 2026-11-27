<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('status')->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('project_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_group_id')->constrained('business_groups')->cascadeOnDelete();
            $table->string('name');
            $table->string('status')->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['business_group_id', 'name']);
        });

        Schema::create('catalog_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_category_id')->constrained('project_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('status')->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['project_category_id', 'name']);
        });

        Schema::create('catalog_product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_room_id')->constrained('catalog_rooms')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('status')->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['catalog_room_id', 'name'], 'cpc_room_name_unique');
        });

        Schema::create('design_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_product_category_id')->constrained('catalog_product_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('status')->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['catalog_product_category_id', 'name'], 'design_types_cpc_name_unique');
        });

        Schema::create('product_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_product_category_id')->constrained('catalog_product_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('status')->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['catalog_product_category_id', 'name'], 'product_models_cpc_name_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_models');
        Schema::dropIfExists('design_types');
        Schema::dropIfExists('catalog_product_categories');
        Schema::dropIfExists('catalog_rooms');
        Schema::dropIfExists('project_categories');
        Schema::dropIfExists('business_groups');
    }
};
