<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->restrictOnDelete();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->string('menu_group', 100)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('image_url')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['parent_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
