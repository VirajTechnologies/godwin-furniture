<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_categories', function (Blueprint $table) {
            $table->boolean('show_on_store')->default(true)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('project_categories', function (Blueprint $table) {
            $table->dropColumn('show_on_store');
        });
    }
};
