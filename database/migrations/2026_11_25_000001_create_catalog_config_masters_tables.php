<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_colours', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('hex', 7);
            $table->string('status')->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_finishes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('status')->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_upholsteries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('status')->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('catalog_config_options', function (Blueprint $table) {
            $table->id();
            $table->string('family_key');
            $table->string('field_key');
            $table->string('name');
            $table->string('status')->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['family_key', 'field_key', 'name'], 'catalog_config_options_unique');
            $table->index(['family_key', 'field_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_config_options');
        Schema::dropIfExists('product_upholsteries');
        Schema::dropIfExists('product_finishes');
        Schema::dropIfExists('product_colours');
    }
};
