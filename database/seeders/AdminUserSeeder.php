<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the head-office admin used to sign in locally.
     */
    public function run(): void
    {
        $role = Role::query()->where('slug', Role::SUPER_ADMIN)->firstOrFail();

        User::query()->updateOrCreate(
            ['email' => 'admin@godwin.test'],
            [
                'name' => 'Godwin Admin',
                'password' => 'password',
                'role_id' => $role->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ],
        );
    }
}
