<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchProductPrice;
use App\Models\Category;
use App\Models\City;
use App\Models\District;
use App\Models\State;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCatalog;
use Tests\TestCase;

class StorefrontCatalogTest extends TestCase
{
    use CreatesCatalog;
    use RefreshDatabase;

    public function test_home_mega_menu_uses_catalog_categories(): void
    {
        [$room, $category] = $this->storefrontRoom();

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('Living Room')
            ->assertSee('3 Seater Sofas')
            ->assertSee('Sofas & Seating')
            ->assertSee('Open Living Room menu', false)
            ->assertSee(route('store.catalog', ['category' => $room->slug], false))
            ->assertSee(route('store.catalog', ['category' => $category->slug], false));

        $this->get(route('store.catalog'))
            ->assertOk()
            ->assertSee('Living Room')
            ->assertSee('3 Seater Sofas')
            ->assertSee('All Living Room')
            ->assertDontSee('form-check-input', false);

        $this->get(route('store.catalog', ['category' => $category->slug]))
            ->assertOk()
            ->assertSee('3 Seater Sofas');

        $this->get(route('store.cart'))
            ->assertOk()
            ->assertSee('Living Room')
            ->assertSee('3 Seater Sofas')
            ->assertSee('Open Living Room menu', false);
    }

    public function test_catalog_lists_online_products_with_mrp(): void
    {
        $product = $this->catalogProduct([
            'code' => 'SF001',
            'slug' => 'alanis-sofa',
            'name' => 'Alanis Metal Frame Sofa',
            'material' => 'CRCA Steel & Velvet',
            'base_price' => 38999,
            'is_online' => true,
            'is_featured' => true,
        ], [
            [
                'colour' => 'Walnut',
                'finish' => 'Matte',
                'upholstery' => 'Velvet',
                'price' => 38999,
                'compare_at_price' => 54999,
            ],
        ]);

        $this->get(route('store.catalog'))
            ->assertOk()
            ->assertSee('Alanis Metal Frame Sofa')
            ->assertSee('38,999');

        $this->get(route('store.product', $product->slug))
            ->assertOk()
            ->assertSee('Alanis Metal Frame Sofa')
            ->assertSee('SF001')
            ->assertSee('38,999')
            ->assertSee('54,999');
    }

    public function test_product_detail_defaults_to_lowest_priced_variant(): void
    {
        Warehouse::query()->create([
            'code' => 'WH001',
            'name' => 'Main Warehouse',
            'address_line' => 'Industrial Area',
            'pincode' => '520001',
            'is_primary' => true,
            'status' => 'active',
        ]);

        $product = $this->catalogProduct([
            'code' => 'SF200',
            'slug' => 'price-order-sofa',
            'name' => 'Price Order Sofa',
            'base_price' => 45000,
            'is_online' => true,
        ], [
            [
                'colour' => 'Walnut',
                'finish' => 'Matte',
                'upholstery' => 'Velvet',
                'price' => 45000,
            ],
            [
                'colour' => 'Charcoal',
                'finish' => 'Matte',
                'upholstery' => 'Velvet',
                'price' => 41000,
            ],
        ]);

        $cheapest = $product->variants->firstWhere('colour', 'Charcoal');

        $html = $this->get(route('store.product', $product->slug))
            ->assertOk()
            ->assertSee('41,000')
            ->assertSee('data-variant-selected="colour">Charcoal', false)
            ->assertSee('value="'.$cheapest->id.'"', false)
            ->getContent();

        preg_match('/id="variant-data">(.*?)<\/script>/s', $html, $matches);
        $this->assertNotEmpty($matches[1] ?? null);
        $payload = json_decode($matches[1], true);
        $this->assertIsArray($payload);
        $this->assertSame(41000.0, (float) $payload[0]['price']);
        $this->assertSame('Charcoal', $payload[0]['colour']);
    }

    public function test_branch_sale_uses_branch_override_price(): void
    {
        $product = $this->catalogProduct([
            'code' => 'SF001',
            'slug' => 'sofa',
            'name' => 'Sofa',
            'base_price' => 42000,
        ]);
        $variant = $this->primaryVariant($product);

        $state = State::query()->create(['state_name' => 'Andhra Pradesh', 'status' => 'active']);
        $district = District::query()->create(['state_id' => $state->id, 'district_name' => 'Krishna', 'status' => 'active']);
        $city = City::query()->create(['state_id' => $state->id, 'district_id' => $district->id, 'city_name' => 'Vijayawada', 'status' => 'active']);
        $warehouse = Warehouse::query()->create([
            'code' => 'WH001',
            'name' => 'Main',
            'address_line' => 'Area',
            'state_id' => $state->id,
            'district_id' => $district->id,
            'city_id' => $city->id,
            'pincode' => '520001',
            'status' => 'active',
        ]);
        $branch = Branch::query()->create([
            'code' => 'BR001',
            'name' => 'Vijayawada Showroom',
            'warehouse_id' => $warehouse->id,
            'address_line' => 'MG Road',
            'state_id' => $state->id,
            'district_id' => $district->id,
            'city_id' => $city->id,
            'pincode' => '520010',
            'status' => 'active',
        ]);

        BranchProductPrice::query()->create([
            'branch_id' => $branch->id,
            'product_variant_id' => $variant->id,
            'price' => 40000,
        ]);

        $variant->refresh();
        $this->assertSame(40000.0, $variant->priceForBranch($branch));

        $variant->update(['price' => 38999]);
        $this->assertSame(38999.0, $variant->fresh()->storePrice());

        $other = Branch::query()->create([
            'code' => 'BR002',
            'name' => 'Guntur Showroom',
            'warehouse_id' => $warehouse->id,
            'address_line' => 'Main',
            'state_id' => $state->id,
            'district_id' => $district->id,
            'city_id' => $city->id,
            'pincode' => '522001',
            'status' => 'active',
        ]);
        $this->assertSame(38999.0, $variant->fresh()->priceForBranch($other));
    }

    /**
     * @return array{0: Category, 1: Category}
     */
    private function storefrontRoom(): array
    {
        $project = Category::query()->create([
            'name' => 'Furniture',
            'slug' => 'furniture-'.uniqid(),
            'status' => 'active',
            'sort_order' => 1,
        ]);
        $room = Category::query()->create([
            'parent_id' => $project->id,
            'name' => 'Living Room',
            'slug' => 'living-room-'.uniqid(),
            'status' => 'active',
            'sort_order' => 1,
        ]);
        $category = Category::query()->create([
            'parent_id' => $room->id,
            'name' => '3 Seater Sofas',
            'slug' => '3-seater-sofas-'.uniqid(),
            'menu_group' => 'Sofas & Seating',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        return [$room, $category];
    }
}
