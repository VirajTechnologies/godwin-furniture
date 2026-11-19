<?php

namespace Tests\Concerns;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\Catalog;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait CreatesCatalog
{
    private function subcategory(string $name = '3 Seater Sofas', string $room = 'Living Room', string $status = 'active'): Category
    {
        $parent = Category::query()->create([
            'name' => $room,
            'slug' => str($room)->slug()->toString().'-'.uniqid(),
            'status' => 'active',
            'sort_order' => 1,
        ]);

        return Category::query()->create([
            'parent_id' => $parent->id,
            'name' => $name,
            'slug' => str($name)->slug()->toString().'-'.uniqid(),
            'menu_group' => 'Sofas & Seating',
            'status' => $status,
            'sort_order' => 1,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @param  list<array<string, mixed>>|null  $variants
     */
    private function catalogProduct(array $overrides = [], ?array $variants = null): Product
    {
        $project = (string) ($overrides['project_category'] ?? 'Furniture');
        $room = (string) ($overrides['room'] ?? 'Living Room');
        $productCategory = (string) ($overrides['product_category'] ?? 'Sofa');
        $designType = (string) ($overrides['design_type'] ?? 'Contemporary');
        $family = (string) ($overrides['family'] ?? Catalog::family($project, $productCategory));
        $basePrice = (float) ($overrides['base_price'] ?? $overrides['price'] ?? 42000);
        $category = $overrides['category'] ?? Catalog::leafCategory($project, $room, $productCategory);

        $name = (string) ($overrides['name'] ?? 'Sample '.$productCategory);
        $code = strtoupper((string) ($overrides['code'] ?? 'P'.fake()->unique()->numerify('####')));

        $product = Product::factory()->create(array_merge([
            'category_id' => $category->id,
            'code' => $code,
            'slug' => (string) ($overrides['slug'] ?? Str::slug($name).'-'.Str::lower($code)),
            'name' => $name,
            'description' => $overrides['description'] ?? null,
            'project_category' => $project,
            'room' => $room,
            'product_category' => $productCategory,
            'design_type' => $designType,
            'family' => $family,
            'material' => $overrides['material'] ?? null,
            'unit' => $overrides['unit'] ?? 'Piece',
            'base_price' => $basePrice,
            'is_online' => (bool) ($overrides['is_online'] ?? false),
            'is_featured' => (bool) ($overrides['is_featured'] ?? false),
            'status' => $overrides['status'] ?? Product::STATUS_ACTIVE,
        ], Arr::only($overrides, [
            'width_cm', 'depth_cm', 'height_cm', 'weight_kg', 'specs',
        ])));

        $rows = $variants ?? ($overrides['variants'] ?? null) ?? [
            $this->defaultVariantRow($family, $basePrice, $overrides['compare_at_price'] ?? null),
        ];

        foreach (array_values($rows) as $index => $row) {
            $price = (float) ($row['price'] ?? $basePrice);
            $upholstery = Catalog::usesUpholstery($family)
                ? (string) ($row['upholstery'] ?? 'Velvet')
                : (string) ($row['upholstery'] ?? '');

            ProductVariant::factory()->create([
                'product_id' => $product->id,
                'sku' => (string) ($row['sku'] ?? $code.'-V'.($index + 1)),
                'colour' => (string) ($row['colour'] ?? 'Walnut'),
                'finish' => (string) ($row['finish'] ?? 'Matte'),
                'upholstery' => $upholstery,
                'price' => $price,
                'compare_at_price' => $row['compare_at_price'] ?? null,
                'status' => $row['status'] ?? ProductVariant::STATUS_ACTIVE,
            ]);
        }

        return $product->load('variants');
    }

    private function primaryVariant(Product $product): ProductVariant
    {
        $product->loadMissing('variants');

        return $product->variants->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultVariantRow(string $family, float $price, mixed $compareAt = null): array
    {
        return [
            'colour' => 'Walnut',
            'finish' => 'Matte',
            'upholstery' => Catalog::usesUpholstery($family) ? 'Velvet' : '',
            'price' => $price,
            'compare_at_price' => $compareAt,
        ];
    }
}
