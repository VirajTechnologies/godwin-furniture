<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\District;
use App\Models\State;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWarehouseTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get(route('admin.warehouses.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_create_the_first_warehouse_as_primary(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'status' => 'active',
        ]);

        [$state, $district, $city] = $this->location();

        $response = $this->actingAs($admin)->post(route('admin.warehouses.store'), [
            'code' => 'wh001',
            'name' => 'Main Warehouse',
            'address_line' => 'Plot 1, Industrial Area',
            'state_id' => $state->id,
            'district_id' => $district->id,
            'city_id' => $city->id,
            'pincode' => '520001',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.warehouses.index'));

        $this->assertDatabaseHas('warehouses', [
            'code' => 'WH001',
            'name' => 'Main Warehouse',
            'state_id' => $state->id,
            'district_id' => $district->id,
            'city_id' => $city->id,
            'is_primary' => true,
            'status' => 'active',
        ]);
    }

    public function test_warehouse_rejects_a_district_from_another_state(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'status' => 'active',
        ]);

        [$state, $district, $city] = $this->location();
        $otherState = State::query()->create([
            'state_name' => 'Telangana',
            'status' => 'active',
        ]);

        $this->actingAs($admin)->post(route('admin.warehouses.store'), [
            'code' => 'WH001',
            'name' => 'Main Warehouse',
            'address_line' => 'Plot 1',
            'state_id' => $otherState->id,
            'district_id' => $district->id,
            'city_id' => $city->id,
            'pincode' => '520001',
            'status' => 'active',
        ])->assertSessionHasErrors('district_id');

        $this->assertDatabaseMissing('warehouses', ['code' => 'WH001']);
    }

    public function test_setting_a_second_warehouse_as_primary_clears_the_previous_one(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'status' => 'active',
        ]);

        $first = Warehouse::query()->create([
            'code' => 'WH001',
            'name' => 'Main Warehouse',
            'address_line' => 'Plot 1',
            'pincode' => '500001',
            'is_primary' => true,
            'status' => 'active',
        ]);

        $second = Warehouse::query()->create([
            'code' => 'WH002',
            'name' => 'District Warehouse',
            'address_line' => 'Plot 2',
            'pincode' => '506002',
            'is_primary' => false,
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.warehouses.primary', $second))
            ->assertRedirect();

        $this->assertFalse($first->fresh()->is_primary);
        $this->assertTrue($second->fresh()->is_primary);
    }

    public function test_primary_warehouse_cannot_be_deactivated(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'status' => 'active',
        ]);

        $warehouse = Warehouse::query()->create([
            'code' => 'WH001',
            'name' => 'Main Warehouse',
            'address_line' => 'Plot 1',
            'pincode' => '500001',
            'is_primary' => true,
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.warehouses.status', $warehouse), ['status' => 'inactive'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('active', $warehouse->fresh()->status);
    }

    /**
     * @return array{0: State, 1: District, 2: City}
     */
    private function location(): array
    {
        $state = State::query()->create([
            'state_name' => 'Andhra Pradesh',
            'status' => 'active',
        ]);
        $district = District::query()->create([
            'state_id' => $state->id,
            'district_name' => 'Krishna',
            'status' => 'active',
        ]);
        $city = City::query()->create([
            'state_id' => $state->id,
            'district_id' => $district->id,
            'city_name' => 'Vijayawada',
            'status' => 'active',
        ]);

        return [$state, $district, $city];
    }
}
