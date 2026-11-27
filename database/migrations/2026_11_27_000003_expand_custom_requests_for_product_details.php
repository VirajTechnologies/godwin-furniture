<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
        });

        Schema::table('custom_requests', function (Blueprint $table) {
            $table->string('match_mode', 20)->default('matched')->after('branch_id');
            $table->foreignId('product_id')->nullable()->after('match_mode')->constrained()->nullOnDelete();
            $table->string('project_category', 80)->nullable()->after('product_id');
            $table->string('room', 80)->nullable()->after('project_category');
            $table->string('product_category', 100)->nullable()->after('room');
            $table->string('design_type', 100)->nullable()->after('product_category');
            $table->string('model_name', 150)->nullable()->after('design_type');
            $table->text('description')->nullable()->after('model_name');
            $table->decimal('width_cm', 8, 2)->nullable()->after('description');
            $table->decimal('depth_cm', 8, 2)->nullable()->after('width_cm');
            $table->decimal('height_cm', 8, 2)->nullable()->after('depth_cm');
            $table->decimal('weight_kg', 8, 2)->nullable()->after('height_cm');
            $table->string('material', 100)->nullable()->after('weight_kg');
            $table->decimal('base_price', 12, 2)->nullable()->after('material');
            $table->json('specs')->nullable()->after('base_price');
            $table->json('variants')->nullable()->after('specs');
        });
    }

    public function down(): void
    {
        Schema::table('custom_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
            $table->dropColumn([
                'match_mode',
                'project_category',
                'room',
                'product_category',
                'design_type',
                'model_name',
                'description',
                'width_cm',
                'depth_cm',
                'height_cm',
                'weight_kg',
                'material',
                'base_price',
                'specs',
                'variants',
            ]);
        });

        Schema::table('custom_requests', function (Blueprint $table) {
            $table->foreignId('product_id')->after('branch_id')->constrained()->restrictOnDelete();
        });
    }
};
