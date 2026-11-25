<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use App\Support\Catalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCatalog;
use Tests\TestCase;

class AdminCatalogTest extends TestCase
{
    use CreatesCatalog;
    use RefreshDatabase;

    public function test_admin_can_create_a_product_with_variants(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'project_category' => 'Furniture',
            'room' => 'Living Room',
            'product_category' => 'Sofa',
            'design_type' => 'Chester',
            'code' => 'sf001',
            'name' => 'Three Seater Sofa',
            'unit' => 'Piece',
            'base_price' => '45000.50',
            'is_online' => '0',
            'is_featured' => '0',
            'status' => 'active',
            'variants' => [
                [
                    'colour' => 'Walnut',
                    'finish' => 'Matte',
                    'upholstery' => 'Velvet',
                    'price' => '45000.50',
                    'compare_at_price' => '54999',
                    'status' => 'active',
                ],
                [
                    'colour' => 'Charcoal',
                    'finish' => 'Matte',
                    'upholstery' => 'Linen blend',
                    'price' => '46000',
                    'compare_at_price' => '55999',
                    'status' => 'active',
                ],
            ],
        ])->assertRedirect(route('admin.products.index'));

        $leaf = Catalog::leafCategory('Furniture', 'Living Room', 'Sofa');

        $this->assertDatabaseHas('products', [
            'code' => 'SF001',
            'name' => 'Three Seater Sofa',
            'category_id' => $leaf->id,
            'project_category' => 'Furniture',
            'room' => 'Living Room',
            'product_category' => 'Sofa',
            'design_type' => 'Chester',
            'family' => 'seat',
            'base_price' => '45000.50',
            'is_online' => false,
            'status' => 'active',
        ]);
        $this->assertSame(2, ProductVariant::query()->where('product_id', Product::query()->where('code', 'SF001')->value('id'))->count());
    }

    public function test_admin_can_create_a_catalog_product_category(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $this->seed(\Database\Seeders\CatalogMasterSeeder::class);

        $room = \App\Models\CatalogRoom::query()->where('name', 'Living Room')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.catalog-product-categories.store'), [
            'catalog_room_id' => $room->id,
            'name' => 'L-Shaped Sofas',
            'sort_order' => 2,
            'status' => 'active',
        ])->assertRedirect(route('admin.catalog-product-categories.index'));

        $this->assertDatabaseHas('catalog_product_categories', [
            'catalog_room_id' => $room->id,
            'name' => 'L-Shaped Sofas',
        ]);
    }

    public function test_store_menu_lists_subcategories_by_sort_order(): void
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

        Category::query()->create([
            'parent_id' => $room->id,
            'name' => 'Zebra Tables',
            'slug' => 'zebra-tables-'.uniqid(),
            'status' => 'active',
            'sort_order' => 2,
        ]);

        Category::query()->create([
            'parent_id' => $room->id,
            'name' => 'Alpha Tables',
            'slug' => 'alpha-tables-'.uniqid(),
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $html = $this->get(route('store.home'))->assertOk()->getContent();
        $alphaPos = strpos($html, 'Alpha Tables');
        $zebraPos = strpos($html, 'Zebra Tables');

        $this->assertNotFalse($alphaPos);
        $this->assertNotFalse($zebraPos);
        $this->assertLessThan($zebraPos, $alphaPos);
        $this->assertStringNotContainsString('Sofas &amp; Seating', $html);
        $this->assertStringNotContainsString('Tables &amp; Storage', $html);
    }

    public function test_catalog_master_indexes_are_reachable(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $this->seed(\Database\Seeders\CatalogMasterSeeder::class);

        $this->actingAs($admin)->get(route('admin.business-groups.index'))->assertOk()->assertSee('Business Groups');
        $this->actingAs($admin)
            ->get(route('admin.project-categories.index'))
            ->assertOk()
            ->assertSee('Furniture')
            ->assertSee('Interiors')
            ->assertSee('Internal');
        $this->actingAs($admin)->get(route('admin.catalog-rooms.index'))->assertOk()->assertSee('Living Room')->assertSee('Kids Room')->assertSee('Internal');
        $this->actingAs($admin)->get(route('admin.catalog-product-categories.index'))->assertOk()->assertSee('Sofa');
        $this->actingAs($admin)->get(route('admin.design-types.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.product-models.index'))->assertOk();
    }

    public function test_product_rejects_an_invalid_catalog_path(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'project_category' => 'Furniture',
            'room' => 'Living Room',
            'product_category' => 'Not A Real Category',
            'design_type' => 'Contemporary',
            'code' => 'BD001',
            'name' => 'Queen Bed',
            'unit' => 'Piece',
            'base_price' => '32000',
            'is_online' => '0',
            'is_featured' => '0',
            'status' => 'active',
            'variants' => [
                [
                    'colour' => 'Walnut',
                    'finish' => 'Matte',
                    'upholstery' => 'Velvet',
                    'price' => '32000',
                    'status' => 'active',
                ],
            ],
        ])->assertSessionHasErrors('product_category');

        $this->assertDatabaseMissing('products', ['code' => 'BD001']);
    }

    public function test_opening_stock_is_recorded_for_a_warehouse(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $product = $this->product();
        $variant = $this->primaryVariant($product);
        $warehouse = $this->warehouse();

        $this->actingAs($admin)->post(route('admin.stocks.store'), [
            'product_variant_id' => $variant->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 5,
            'note' => 'Opening count',
        ])->assertRedirect(route('admin.stocks.index'));

        $this->assertDatabaseHas('stocks', [
            'product_variant_id' => $variant->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 5,
            'branch_id' => null,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'type' => StockMovement::TYPE_OPENING,
            'quantity_change' => 5,
            'quantity_after' => 5,
            'user_id' => $admin->id,
        ]);
    }

    public function test_the_same_variant_cannot_be_stocked_twice_in_one_warehouse(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $product = $this->product();
        $variant = $this->primaryVariant($product);
        $warehouse = $this->warehouse();

        $this->actingAs($admin)->post(route('admin.stocks.store'), [
            'product_variant_id' => $variant->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 2,
        ])->assertRedirect(route('admin.stocks.index'));

        $this->actingAs($admin)->post(route('admin.stocks.store'), [
            'product_variant_id' => $variant->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 3,
        ])->assertSessionHasErrors('product_variant_id');

        $this->assertSame(1, $variant->stocks()->count());
    }

    public function test_changing_quantity_writes_an_adjustment(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $product = $this->product();
        $variant = $this->primaryVariant($product);
        $warehouse = $this->warehouse();

        $this->actingAs($admin)->post(route('admin.stocks.store'), [
            'product_variant_id' => $variant->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 5,
        ]);

        $stock = $variant->stocks()->firstOrFail();

        $this->actingAs($admin)->put(route('admin.stocks.update', $stock), [
            'direction' => 'add',
            'adjust_quantity' => 3,
            'note' => 'Counted extra pieces',
        ])->assertRedirect(route('admin.stocks.index'));

        $this->assertDatabaseHas('stocks', [
            'id' => $stock->id,
            'quantity' => 8,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'stock_id' => $stock->id,
            'type' => StockMovement::TYPE_ADJUSTMENT,
            'quantity_change' => 3,
            'quantity_after' => 8,
        ]);
    }

    public function test_removing_more_than_the_quantity_on_hand_is_rejected(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $product = $this->product();
        $variant = $this->primaryVariant($product);
        $warehouse = $this->warehouse();

        $this->actingAs($admin)->post(route('admin.stocks.store'), [
            'product_variant_id' => $variant->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 5,
        ]);

        $stock = $variant->stocks()->firstOrFail();

        $this->actingAs($admin)->put(route('admin.stocks.update', $stock), [
            'direction' => 'remove',
            'adjust_quantity' => 8,
            'note' => 'Damaged pieces',
        ])->assertSessionHasErrors('adjust_quantity');

        $this->assertDatabaseHas('stocks', [
            'id' => $stock->id,
            'quantity' => 5,
        ]);
    }

    public function test_the_product_list_includes_every_product_for_datatables(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $first = $this->product();
        $second = $this->catalogProduct([
            'code' => 'BD001',
            'slug' => 'teak-bed',
            'name' => 'Queen Size Teak Bed',
            'room' => 'Bedroom',
            'product_category' => 'Queen Size Bed',
            'design_type' => 'Platform',
            'base_price' => 36000,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee($first->name)
            ->assertSee($second->name)
            ->assertSee('js-datatable', false)
            ->assertSee('dataTables.bootstrap5.min.js', false)
            ->assertSee('name="q"', false)
            ->assertSee('name="status"', false);
    }

    public function test_the_product_list_can_be_filtered(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $sofa = $this->product();
        $bed = $this->catalogProduct([
            'code' => 'BD001',
            'slug' => 'teak-bed',
            'name' => 'Queen Size Teak Bed',
            'room' => 'Bedroom',
            'product_category' => 'Queen Size Bed',
            'design_type' => 'Platform',
            'base_price' => 36000,
            'is_online' => true,
            'status' => Product::STATUS_INACTIVE,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.products.index', ['q' => 'BD001']))
            ->assertOk()
            ->assertSee($bed->name)
            ->assertDontSee($sofa->name);

        $this->actingAs($admin)
            ->get(route('admin.products.index', ['room' => 'Bedroom']))
            ->assertOk()
            ->assertSee($bed->name)
            ->assertDontSee($sofa->name);

        $this->actingAs($admin)
            ->get(route('admin.products.index', ['status' => 'inactive', 'online' => '1']))
            ->assertOk()
            ->assertSee($bed->name)
            ->assertDontSee($sofa->name);

        $this->actingAs($admin)
            ->get(route('admin.products.index', ['q' => 'no-such-product']))
            ->assertOk()
            ->assertSee('No products match')
            ->assertDontSee($sofa->name)
            ->assertDontSee($bed->name);
    }

    public function test_catalog_room_create_form_lists_projects(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $this->seed(\Database\Seeders\CatalogMasterSeeder::class);

        $this->actingAs($admin)
            ->get(route('admin.catalog-rooms.create'))
            ->assertOk()
            ->assertSee('Project')
            ->assertSee('Furniture');
    }

    private function product(): Product
    {
        return $this->catalogProduct([
            'code' => 'SF001',
            'slug' => 'three-seater-sofa',
            'name' => 'Three Seater Sofa',
            'base_price' => 45000,
        ]);
    }

    private function warehouse(): Warehouse
    {
        return Warehouse::query()->create([
            'code' => 'WH001',
            'name' => 'Main Warehouse',
            'address_line' => 'Industrial Area',
            'pincode' => '520001',
            'status' => 'active',
        ]);
    }
}
