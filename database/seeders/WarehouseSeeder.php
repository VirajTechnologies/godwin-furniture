<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\District;
use App\Models\State;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Seed the main warehouse in Vijayawada.
     */
    public function run(): void
    {
        $state = State::query()->where('state_name', 'Andhra Pradesh')->firstOrFail();
        $district = District::query()
            ->where('state_id', $state->id)
            ->where('district_name', 'Krishna')
            ->firstOrFail();
        $city = City::query()
            ->where('district_id', $district->id)
            ->where('city_name', 'Vijayawada')
            ->firstOrFail();

        Warehouse::query()->updateOrCreate(
            ['code' => 'WH001'],
            [
                'name' => 'Main Warehouse',
                'contact_person' => 'Store Keeper',
                'phone' => '9876543210',
                'address_line' => 'Industrial Area, Plot 1',
                'state_id' => $state->id,
                'district_id' => $district->id,
                'city_id' => $city->id,
                'pincode' => '520001',
                'status' => Warehouse::STATUS_ACTIVE,
            ],
        );
    }
}
