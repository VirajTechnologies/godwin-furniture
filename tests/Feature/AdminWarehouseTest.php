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

    public function test_admin_can_create_a_warehouse(): void
    {
        $admin = User::factory()->superAdmin()->create();

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
            'status' => 'active',
        ]);
    }

    public function test_warehouse_rejects_a_district_from_another_state(): void
    {
        $admin = User::factory()->superAdmin()->create();

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

    public function test_admin_can_deactivate_a_warehouse(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $warehouse = Warehouse::query()->create([
            'code' => 'WH001',
            'name' => 'Main Warehouse',
            'address_line' => 'Plot 1',
            'pincode' => '500001',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.warehouses.status', $warehouse), ['status' => 'inactive'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('inactive', $warehouse->fresh()->status);
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
