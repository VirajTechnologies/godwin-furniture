<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CitySeeder;
use Database\Seeders\DistrictSeeder;
use Database\Seeders\StateSeeder;
use Database\Seeders\WarehouseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationMasterTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_create_andhra_pradesh_krishna_and_vijayawada(): void
    {
        $this->seed(StateSeeder::class);
        $this->seed(DistrictSeeder::class);
        $this->seed(CitySeeder::class);
        $this->seed(WarehouseSeeder::class);

        $this->assertDatabaseHas('states', ['state_name' => 'Andhra Pradesh', 'status' => 'active']);
        $this->assertDatabaseHas('districts', ['district_name' => 'Krishna', 'status' => 'active']);
        $this->assertDatabaseHas('cities', ['city_name' => 'Vijayawada', 'status' => 'active']);
        $this->assertDatabaseHas('warehouses', [
            'code' => 'WH001',
            'name' => 'Main Warehouse',
            'is_primary' => true,
            'city_id' => \App\Models\City::query()->where('city_name', 'Vijayawada')->value('id'),
        ]);
    }

    public function test_district_options_are_limited_to_the_selected_state(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'status' => 'active',
        ]);

        $this->seed(StateSeeder::class);
        $this->seed(DistrictSeeder::class);

        $stateId = \App\Models\State::query()->where('state_name', 'Andhra Pradesh')->value('id');
        $other = \App\Models\State::query()->create([
            'state_name' => 'Telangana',
            'status' => 'active',
        ]);
        \App\Models\District::query()->create([
            'state_id' => $other->id,
            'district_name' => 'Hyderabad',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.districts.options', ['state_id' => $stateId]))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['district_name' => 'Krishna'])
            ->assertJsonMissing(['district_name' => 'Hyderabad']);
    }

    public function test_deactivating_a_state_keeps_the_row(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'status' => 'active',
        ]);

        $this->seed(StateSeeder::class);
        $state = \App\Models\State::query()->where('state_name', 'Andhra Pradesh')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.states.status', $state), ['status' => 'inactive'])
            ->assertRedirect();

        $this->assertDatabaseHas('states', [
            'id' => $state->id,
            'state_name' => 'Andhra Pradesh',
            'status' => 'inactive',
        ]);
    }
}