<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the head-office admin used to sign in locally.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@godwin.test'],
            [
                'name' => 'Godwin Admin',
                'password' => 'password',
                'role' => User::ROLE_SUPER_ADMIN,
                'status' => 'active',
                'email_verified_at' => now(),
            ],
        );
    }
}
