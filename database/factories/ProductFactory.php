<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $name = fake()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'code' => strtoupper(fake()->unique()->bothify('??###')),
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'name' => $name,
            'description' => null,
            'project_category' => 'Furniture',
            'room' => 'Living Room',
            'product_category' => 'Sofa',
            'design_type' => 'Contemporary',
            'family' => 'seat',
            'width_cm' => 200,
            'depth_cm' => 90,
            'height_cm' => 85,
            'weight_kg' => 45,
            'specs' => null,
            'material' => null,
            'unit' => 'Piece',
            'base_price' => fake()->numberBetween(5000, 90000),
            'is_online' => false,
            'is_featured' => false,
            'status' => Product::STATUS_ACTIVE,
        ];
    }
}
