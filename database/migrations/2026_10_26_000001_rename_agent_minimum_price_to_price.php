<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_product_terms', function (Blueprint $table) {
            $table->renameColumn('minimum_price', 'price');
        });

        Schema::table('agent_product_terms', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->nullable()->change();
            $table->decimal('commission_percent', 5, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('agent_product_terms', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->nullable(false)->change();
            $table->decimal('commission_percent', 5, 2)->nullable(false)->change();
        });

        Schema::table('agent_product_terms', function (Blueprint $table) {
            $table->renameColumn('price', 'minimum_price');
        });
    }
};
