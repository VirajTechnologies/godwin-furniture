<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_customers_list_and_detail(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $customer = Customer::factory()->create([
            'name' => 'Ravi Kumar',
            'phone' => '9876501234',
            'email' => 'ravi@example.com',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.customers.index'))
            ->assertOk()
            ->assertSee('Registration')
            ->assertSee('View only')
            ->assertSee('Ravi Kumar')
            ->assertSee('Customer');

        $this->actingAs($admin)
            ->get(route('admin.customers.show', $customer))
            ->assertOk()
            ->assertSee('Ravi Kumar')
            ->assertSee('9876501234')
            ->assertSee('ravi@example.com');
    }

    public function test_registration_tabs_appear_on_branch_index(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.branches.index'))
            ->assertOk()
            ->assertSee('Registration')
            ->assertSee('Warehouse')
            ->assertSee('Employee')
            ->assertSee('Agent')
            ->assertSee('Customer');
    }
}
