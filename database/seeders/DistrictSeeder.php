<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\State;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    /**
     * Seed Krishna under Andhra Pradesh.
     */
    public function run(): void
    {
        $state = State::query()->where('state_name', 'Andhra Pradesh')->firstOrFail();

        District::query()->updateOrCreate(
            [
                'state_id' => $state->id,
                'district_name' => 'Krishna',
            ],
            ['status' => District::STATUS_ACTIVE],
        );
    }
}
