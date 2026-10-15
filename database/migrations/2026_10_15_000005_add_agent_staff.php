<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('agent_id')->nullable()->after('role_id')->constrained()->nullOnDelete();
        });

        DB::table('agents')->select('id', 'user_id')->orderBy('id')->each(function (object $agent): void {
            DB::table('users')->where('id', $agent->user_id)->update(['agent_id' => $agent->id]);
        });

        DB::table('roles')->where('slug', 'agent')->update(['name' => 'Agent Manager']);

        DB::table('roles')->updateOrInsert(
            ['slug' => 'agent_staff'],
            [
                'name' => 'Agent Cashier',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('agent_id');
        });

        DB::table('roles')->where('slug', 'agent_staff')->delete();
        DB::table('roles')->where('slug', 'agent')->update(['name' => 'Agent']);
    }
};
