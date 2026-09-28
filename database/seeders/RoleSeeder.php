<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Seed the head-office role. Permissions are attached to roles later.
     */
    public function run(): void
    {
        Role::query()->updateOrCreate(
            ['slug' => Role::SUPER_ADMIN],
            [
                'name' => 'Super Admin',
                'status' => Role::STATUS_ACTIVE,
            ],
        );

        Role::query()->updateOrCreate(
            ['slug' => Role::WAREHOUSE_STAFF],
            [
                'name' => 'Warehouse Staff',
                'status' => Role::STATUS_ACTIVE,
            ],
        );
    }
}
