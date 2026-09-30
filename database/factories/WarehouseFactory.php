<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\District;
use App\Models\State;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Warehouse>
 */
class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('WH###')),
            'name' => fake()->city().' Warehouse',
            'contact_person' => fake()->name(),
            'phone' => fake()->numerify('9#########'),
            'email' => fake()->unique()->safeEmail(),
            'address_line' => fake()->streetAddress(),
            'state_id' => State::factory(),
            'district_id' => District::factory(),
            'city_id' => City::factory(),
            'pincode' => fake()->numerify('52####'),
            'is_primary' => false,
            'status' => Warehouse::STATUS_ACTIVE,
        ];
    }
}
