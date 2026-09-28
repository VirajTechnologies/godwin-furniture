<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\District;
use App\Models\State;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBranchTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_branch_supplied_by_a_warehouse(): void
    {
        $admin = User::factory()->superAdmin()->create();
        [$state, $district, $city, $warehouse] = $this->place();

        $this->actingAs($admin)->post(route('admin.branches.store'), [
            'code' => 'br001',
            'name' => 'Vijayawada Showroom',
            'warehouse_id' => $warehouse->id,
            'phone' => '9876543210',
            'address_line' => 'MG Road',
            'state_id' => $state->id,
            'district_id' => $district->id,
            'city_id' => $city->id,
            'pincode' => '520001',
            'status' => 'active',
        ])->assertRedirect(route('admin.branches.index'));

        $this->assertDatabaseHas('branches', [
            'code' => 'BR001',
            'name' => 'Vijayawada Showroom',
            'warehouse_id' => $warehouse->id,
            'city_id' => $city->id,
            'status' => 'active',
        ]);
    }

    public function test_branch_rejects_a_district_from_another_state(): void
    {
        $admin = User::factory()->superAdmin()->create();
        [$state, $district, $city, $warehouse] = $this->place();
        $otherState = State::query()->create([
            'state_name' => 'Telangana',
            'status' => 'active',
        ]);

        $this->actingAs($admin)->post(route('admin.branches.store'), [
            'code' => 'BR001',
            'name' => 'Vijayawada Showroom',
            'warehouse_id' => $warehouse->id,
            'address_line' => 'MG Road',
            'state_id' => $otherState->id,
            'district_id' => $district->id,
            'city_id' => $city->id,
            'pincode' => '520001',
            'status' => 'active',
        ])->assertSessionHasErrors('district_id');

        $this->assertDatabaseMissing('branches', ['code' => 'BR001']);
    }

    /**
     * @return array{0: State, 1: District, 2: City, 3: Warehouse}
     */
    private function place(): array
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
        $warehouse = Warehouse::query()->create([
            'code' => 'WH001',
            'name' => 'Main Warehouse',
            'address_line' => 'Industrial Area',
            'state_id' => $state->id,
            'district_id' => $district->id,
            'city_id' => $city->id,
            'pincode' => '520001',
            'is_primary' => true,
            'status' => 'active',
        ]);

        return [$state, $district, $city, $warehouse];
    }
}
