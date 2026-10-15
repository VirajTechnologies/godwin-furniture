<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('user_id')->constrained()->restrictOnDelete();
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->foreignId('agent_id')->nullable()->after('branch_id')->constrained()->restrictOnDelete();
            $table->unique(['product_id', 'agent_id']);
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->change();
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreignId('agent_id')->nullable()->after('branch_id')->constrained()->restrictOnDelete();
        });

        Schema::table('stock_requests', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
        });

        Schema::table('stock_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->change();
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreignId('agent_id')->nullable()->after('branch_id')->constrained()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('agent_id');
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('agent_id');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropUnique(['product_id', 'agent_id']);
            $table->dropConstrainedForeignId('agent_id');
        });

        Schema::table('agents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('warehouse_id');
        });
    }
};
