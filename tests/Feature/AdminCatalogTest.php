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

    public function test_admin_can_create_a_subcategory_with_an_existing_menu_group(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $existing = $this->subcategory();

        $this->actingAs($admin)->post(route('admin.categories.store'), [
            'parent_id' => $existing->parent_id,
            'name' => 'L-Shaped Sofas',
            'menu_group_choice' => 'Sofas & Seating',
            'sort_order' => 2,
            'status' => 'active',
        ])->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'parent_id' => $existing->parent_id,
            'name' => 'L-Shaped Sofas',
            'menu_group' => 'Sofas & Seating',
        ]);
    }

    public function test_admin_can_create_a_subcategory_with_a_new_menu_group(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $room = Category::query()->create([
            'name' => 'Living Room',
            'slug' => 'living-room-'.uniqid(),
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $this->actingAs($admin)->post(route('admin.categories.store'), [
            'parent_id' => $room->id,
            'name' => 'Coffee Tables',
            'menu_group_choice' => '__new__',
            'menu_group' => 'Tables & Storage',
            'sort_order' => 1,
            'status' => 'active',
        ])->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'parent_id' => $room->id,
            'name' => 'Coffee Tables',
            'menu_group' => 'Tables & Storage',
        ]);
    }

    public function test_menu_groups_appear_alphabetically_with_subcategories_by_sort_order(): void
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
            'menu_group' => 'Tables & Storage',
            'status' => 'active',
            'sort_order' => 2,
        ]);

        Category::query()->create([
            'parent_id' => $room->id,
            'name' => 'Alpha Tables',
            'slug' => 'alpha-tables-'.uniqid(),
            'menu_group' => 'Tables & Storage',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        Category::query()->create([
            'parent_id' => $room->id,
            'name' => '3 Seater Sofas',
            'slug' => '3-seater-sofas-'.uniqid(),
            'menu_group' => 'Sofas & Seating',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $html = $this->get(route('store.home'))->assertOk()->getContent();
        $sofasPos = strpos($html, 'Sofas &amp; Seating');
        $tablesPos = strpos($html, 'Tables &amp; Storage');
        $alphaPos = strpos($html, 'Alpha Tables');
        $zebraPos = strpos($html, 'Zebra Tables');

        $this->assertNotFalse($sofasPos);
        $this->assertNotFalse($tablesPos);
        $this->assertLessThan($tablesPos, $sofasPos);
        $this->assertNotFalse($alphaPos);
        $this->assertNotFalse($zebraPos);
        $this->assertLessThan($zebraPos, $alphaPos);
    }

    public function test_a_room_does_not_keep_a_menu_group(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->post(route('admin.categories.store'), [
            'parent_id' => '',
            'name' => 'Dining Room',
            'menu_group_choice' => '__new__',
            'menu_group' => 'Should Be Cleared',
            'status' => 'active',
        ])->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Dining Room',
            'parent_id' => null,
            'menu_group' => null,
        ]);
    }

    public function test_categories_index_shows_a_room_tree_with_filters(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $sofas = $this->subcategory();
        $room = $sofas->parent;

        Category::query()->create([
            'parent_id' => $room->id,
            'name' => 'Coffee Tables',
            'slug' => 'coffee-tables-'.uniqid(),
            'menu_group' => 'Tables & Storage',
            'status' => 'active',
            'sort_order' => 2,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('Living Room')
            ->assertSee('3 Seater Sofas')
            ->assertSee('Sofas & Seating')
            ->assertSee('Coffee Tables')
            ->assertSee('Category')
            ->assertSee('Add Subcategory');

        $this->actingAs($admin)
            ->get(route('admin.categories.index', ['q' => 'Coffee']))
            ->assertOk()
            ->assertSee('Coffee Tables')
            ->assertDontSee('3 Seater Sofas');

        $this->actingAs($admin)
            ->get(route('admin.categories.index', ['type' => 'room']))
            ->assertOk()
            ->assertSee('Living Room')
            ->assertDontSee('Coffee Tables');
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
            ->assertSee('dataTables.bootstrap5.min.js', false);
    }

    public function test_add_subcategory_link_preselects_the_room(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $room = Category::query()->create([
            'name' => 'Bedroom',
            'slug' => 'bedroom-'.uniqid(),
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.categories.create', ['parent_id' => $room->id]))
            ->assertOk()
            ->assertSee('selected', false)
            ->assertSee((string) $room->id);
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
