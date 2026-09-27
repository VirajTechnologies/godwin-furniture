<?php

namespace Database\Seeders;

use App\Models\State;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    /**
     * Seed the starting state.
     */
    public function run(): void
    {
        State::query()->updateOrCreate(
            ['state_name' => 'Andhra Pradesh'],
            ['status' => State::STATUS_ACTIVE],
        );
    }
}
