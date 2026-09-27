<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn(['city', 'district', 'state']);
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->foreignId('state_id')->nullable()->after('address_line')->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->after('state_id')->constrained()->nullOnDelete();
            $table->foreignId('city_id')->nullable()->after('district_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('state_id');
            $table->dropConstrainedForeignId('district_id');
            $table->dropConstrainedForeignId('city_id');
            $table->string('city')->after('address_line');
            $table->string('district')->after('city');
            $table->string('state')->after('district');
        });
    }
};
