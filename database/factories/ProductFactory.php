<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'code' => strtoupper(fake()->unique()->bothify('??###')),
            'name' => fake()->words(3, true),
            'description' => null,
            'unit' => 'Piece',
            'selling_price' => fake()->numberBetween(5000, 90000),
            'is_online' => false,
            'status' => Product::STATUS_ACTIVE,
        ];
    }
}
