<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\City;
use App\Models\District;
use App\Models\State;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    protected $model = Branch::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('BR###')),
            'name' => fake()->city().' Showroom',
            'warehouse_id' => Warehouse::factory(),
            'contact_person' => fake()->name(),
            'phone' => fake()->numerify('9#########'),
            'email' => fake()->unique()->safeEmail(),
            'address_line' => fake()->streetAddress(),
            'state_id' => State::factory(),
            'district_id' => District::factory(),
            'city_id' => City::factory(),
            'pincode' => fake()->numerify('52####'),
            'status' => Branch::STATUS_ACTIVE,
        ];
    }
}
