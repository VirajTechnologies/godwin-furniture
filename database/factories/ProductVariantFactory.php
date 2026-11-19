<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-????##')),
            'colour' => 'Walnut',
            'colour_hex' => '#5c3d2a',
            'finish' => 'Matte',
            'upholstery' => 'Velvet',
            'price' => fake()->numberBetween(5000, 90000),
            'compare_at_price' => null,
            'status' => ProductVariant::STATUS_ACTIVE,
        ];
    }
}
