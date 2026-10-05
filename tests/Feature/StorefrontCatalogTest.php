<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchProductPrice;
use App\Models\Category;
use App\Models\City;
use App\Models\District;
use App\Models\Product;
use App\Models\State;
use App\Models\User;
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
        $category = $this->subcategory();

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('Living Room')
            ->assertSee('3 Seater Sofas')
            ->assertSee('Sofas & Seating')
            ->assertSee('Open Living Room menu', false)
            ->assertSee(route('store.catalog', ['category' => $category->parent->slug], false))
            ->assertSee(route('store.catalog', ['category' => $category->slug], false));

        $this->get(route('store.catalog'))
            ->assertOk()
            ->assertSee('Living Room')
            ->assertSee('3 Seater Sofas');
    }

    public function test_catalog_lists_online_products_with_mrp(): void
    {
        $category = $this->subcategory();
        $product = Product::query()->create([
            'category_id' => $category->id,
            'code' => 'SF001',
            'slug' => 'alanis-sofa',
            'name' => 'Alanis Metal Frame Sofa',
            'material' => 'CRCA Steel & Velvet',
            'unit' => 'Piece',
            'selling_price' => 42000,
            'online_price' => 38999,
            'compare_at_price' => 54999,
            'is_online' => true,
            'is_featured' => true,
            'status' => 'active',
        ]);

        $this->get(route('store.catalog'))
            ->assertOk()
            ->assertSee('Alanis Metal Frame Sofa')
            ->assertSee('38,999')
            ->assertSee('54,999');

        $this->get(route('store.product', $product->slug))
            ->assertOk()
            ->assertSee('Alanis Metal Frame Sofa')
            ->assertSee('SF001')
            ->assertSee('38,999');
    }

    public function test_branch_sale_uses_branch_override_price(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $category = $this->subcategory();
        $product = Product::query()->create([
            'category_id' => $category->id,
            'code' => 'SF001',
            'slug' => 'sofa',
            'name' => 'Sofa',
            'unit' => 'Piece',
            'selling_price' => 42000,
            'status' => 'active',
        ]);

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
            'is_primary' => true,
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
            'product_id' => $product->id,
            'price' => 40000,
        ]);

        $product->refresh();
        $this->assertSame(40000.0, $product->priceForBranch($branch));

        $product->update(['online_price' => 38999]);
        $this->assertSame(38999.0, $product->fresh()->storePrice());

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
        $this->assertSame(42000.0, $product->fresh()->priceForBranch($other));
    }
}
