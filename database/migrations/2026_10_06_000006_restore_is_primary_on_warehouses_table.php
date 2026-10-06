<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('warehouses', 'is_primary')) {
            Schema::table('warehouses', function (Blueprint $table) {
                $table->boolean('is_primary')->default(false)->after('pincode');
            });
        }

        $hasPrimary = DB::table('warehouses')->where('is_primary', true)->exists();

        if (! $hasPrimary) {
            $firstId = DB::table('warehouses')
                ->where('status', 'active')
                ->orderBy('id')
                ->value('id');

            if ($firstId) {
                DB::table('warehouses')->where('id', $firstId)->update(['is_primary' => true]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('warehouses', 'is_primary')) {
            return;
        }

        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn('is_primary');
        });
    }
};
