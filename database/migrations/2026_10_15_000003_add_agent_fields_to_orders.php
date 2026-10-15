<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('agent_id')->nullable()->after('branch_id')->constrained()->restrictOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('commission_percent', 5, 2)->nullable()->after('line_total');
            $table->decimal('commission_amount', 12, 2)->nullable()->after('commission_percent');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['commission_percent', 'commission_amount']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('agent_id');
        });
    }
};
