<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_warehouse_staff_linked_to_a_warehouse(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $role = Role::query()->create([
            'name' => 'Warehouse Staff',
            'slug' => Role::WAREHOUSE_STAFF,
            'status' => 'active',
        ]);
        $warehouse = Warehouse::query()->create([
            'code' => 'WH001',
            'name' => 'Main Warehouse',
            'address_line' => 'Industrial Area',
            'pincode' => '520001',
            'is_primary' => true,
            'status' => 'active',
        ]);

        $this->actingAs($admin)->post(route('admin.employees.store'), [
            'employee_code' => 'emp001',
            'name' => 'Store Keeper',
            'email' => 'keeper@godwin.test',
            'phone' => '9876543210',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role_id' => $role->id,
            'warehouse_id' => $warehouse->id,
            'designation' => 'Store Keeper',
            'status' => 'active',
        ])->assertRedirect(route('admin.employees.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'keeper@godwin.test',
            'role_id' => $role->id,
            'phone' => '9876543210',
        ]);
        $this->assertDatabaseHas('employee_profiles', [
            'employee_code' => 'EMP001',
            'warehouse_id' => $warehouse->id,
            'status' => 'active',
        ]);
    }

    public function test_warehouse_staff_requires_a_warehouse(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $role = Role::query()->create([
            'name' => 'Warehouse Staff',
            'slug' => Role::WAREHOUSE_STAFF,
            'status' => 'active',
        ]);

        $this->actingAs($admin)->post(route('admin.employees.store'), [
            'employee_code' => 'EMP002',
            'name' => 'Store Keeper',
            'email' => 'keeper@godwin.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role_id' => $role->id,
            'status' => 'active',
        ])->assertSessionHasErrors('warehouse_id');
    }
}
