<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\District;
use App\Models\State;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Seed Vijayawada under Krishna and Andhra Pradesh.
     */
    public function run(): void
    {
        $state = State::query()->where('state_name', 'Andhra Pradesh')->firstOrFail();
        $district = District::query()
            ->where('state_id', $state->id)
            ->where('district_name', 'Krishna')
            ->firstOrFail();

        City::query()->updateOrCreate(
            [
                'district_id' => $district->id,
                'city_name' => 'Vijayawada',
            ],
            [
                'state_id' => $state->id,
                'status' => City::STATUS_ACTIVE,
            ],
        );
    }
}
