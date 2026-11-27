<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            if (! Schema::hasColumn('branches', 'is_primary')) {
                $table->boolean('is_primary')->default(false)->after('pincode');
            }
        });

        $hasPrimary = DB::table('branches')->where('is_primary', true)->exists();

        if (! $hasPrimary) {
            $firstActiveId = DB::table('branches')
                ->where('status', 'active')
                ->orderBy('id')
                ->value('id');

            if ($firstActiveId) {
                DB::table('branches')->where('id', $firstActiveId)->update(['is_primary' => true]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            if (Schema::hasColumn('branches', 'is_primary')) {
                $table->dropColumn('is_primary');
            }
        });
    }
};
