<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_product_in_a_category(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $category = Category::query()->create([
            'name' => 'Sofas',
            'status' => 'active',
        ]);

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'code' => 'sf001',
            'name' => 'Three Seater Sofa',
            'category_id' => $category->id,
            'unit' => 'Piece',
            'selling_price' => '45000.50',
            'status' => 'active',
        ])->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'code' => 'SF001',
            'name' => 'Three Seater Sofa',
            'category_id' => $category->id,
            'selling_price' => '45000.50',
            'is_online' => false,
            'status' => 'active',
        ]);
    }

    public function test_product_rejects_an_inactive_category(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $category = Category::query()->create([
            'name' => 'Beds',
            'status' => 'inactive',
        ]);

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'code' => 'BD001',
            'name' => 'Queen Bed',
            'category_id' => $category->id,
            'unit' => 'Piece',
            'selling_price' => '32000',
            'status' => 'active',
        ])->assertSessionHasErrors('category_id');

        $this->assertDatabaseMissing('products', ['code' => 'BD001']);
    }

    public function test_opening_stock_is_recorded_for_a_warehouse(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $product = $this->product();
        $warehouse = $this->warehouse();

        $this->actingAs($admin)->post(route('admin.stocks.store'), [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 5,
            'note' => 'Opening count',
        ])->assertRedirect(route('admin.stocks.index'));

        $this->assertDatabaseHas('stocks', [
            'product_id' => $product->id,
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

    public function test_the_same_product_cannot_be_stocked_twice_in_one_warehouse(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $product = $this->product();
        $warehouse = $this->warehouse();

        $this->actingAs($admin)->post(route('admin.stocks.store'), [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 2,
        ])->assertRedirect(route('admin.stocks.index'));

        $this->actingAs($admin)->post(route('admin.stocks.store'), [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 3,
        ])->assertSessionHasErrors('product_id');

        $this->assertSame(1, $product->stocks()->count());
    }

    public function test_changing_quantity_writes_an_adjustment(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $product = $this->product();
        $warehouse = $this->warehouse();

        $this->actingAs($admin)->post(route('admin.stocks.store'), [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 5,
        ]);

        $stock = $product->stocks()->firstOrFail();

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
        $warehouse = $this->warehouse();

        $this->actingAs($admin)->post(route('admin.stocks.store'), [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 5,
        ]);

        $stock = $product->stocks()->firstOrFail();

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

    private function product(): Product
    {
        $category = Category::query()->create([
            'name' => 'Sofas',
            'status' => 'active',
        ]);

        return Product::query()->create([
            'category_id' => $category->id,
            'code' => 'SF001',
            'name' => 'Three Seater Sofa',
            'unit' => 'Piece',
            'selling_price' => 45000,
            'is_online' => false,
            'status' => 'active',
        ]);
    }

    private function warehouse(): Warehouse
    {
        return Warehouse::query()->create([
            'code' => 'WH001',
            'name' => 'Main Warehouse',
            'address_line' => 'Industrial Area',
            'pincode' => '520001',
            'is_primary' => true,
            'status' => 'active',
        ]);
    }
}
