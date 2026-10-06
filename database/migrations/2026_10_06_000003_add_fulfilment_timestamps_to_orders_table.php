<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('confirmed_at')->nullable()->after('shipping_pincode');
            $table->timestamp('dispatched_at')->nullable()->after('confirmed_at');
            $table->timestamp('completed_at')->nullable()->after('dispatched_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['confirmed_at', 'dispatched_at', 'completed_at']);
        });
    }
};
