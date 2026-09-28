<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\City;
use App\Models\District;
use App\Models\Product;
use App\Models\State;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStockTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_draft_does_not_change_warehouse_stock(): void
    {
        $admin = User::factory()->superAdmin()->create();
        [$product, $warehouse, $branch] = $this->stocked(5);

        $this->actingAs($admin)->post(route('admin.transfers.store'), [
            'code' => 'tr001',
            'branch_id' => $branch->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('stock_transfers', [
            'code' => 'TR001',
            'warehouse_id' => $warehouse->id,
            'branch_id' => $branch->id,
            'status' => 'draft',
        ]);
        $this->assertDatabaseHas('stocks', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 5,
        ]);
        $this->assertDatabaseMissing('stocks', [
            'product_id' => $product->id,
            'branch_id' => $branch->id,
        ]);
    }

    public function test_dispatch_reduces_warehouse_stock_and_receive_adds_branch_stock(): void
    {
        $admin = User::factory()->superAdmin()->create();
        [$product, $warehouse, $branch] = $this->stocked(5);

        $this->actingAs($admin)->post(route('admin.transfers.store'), [
            'code' => 'TR001',
            'branch_id' => $branch->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $transferId = \App\Models\StockTransfer::query()->where('code', 'TR001')->value('id');

        $this->actingAs($admin)
            ->post(route('admin.transfers.dispatch', $transferId))
            ->assertRedirect(route('admin.transfers.edit', $transferId));

        $this->assertDatabaseHas('stocks', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 3,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'type' => StockMovement::TYPE_TRANSFER_OUT,
            'quantity_change' => -2,
            'quantity_after' => 3,
        ]);
        $this->assertDatabaseMissing('stocks', [
            'product_id' => $product->id,
            'branch_id' => $branch->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.transfers.receive', $transferId))
            ->assertRedirect(route('admin.transfers.edit', $transferId));

        $this->assertDatabaseHas('stock_transfers', [
            'id' => $transferId,
            'status' => 'received',
        ]);
        $this->assertDatabaseHas('stocks', [
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'type' => StockMovement::TYPE_TRANSFER_IN,
            'quantity_change' => 2,
            'quantity_after' => 2,
        ]);
    }

    public function test_dispatch_is_rejected_when_the_warehouse_does_not_have_enough(): void
    {
        $admin = User::factory()->superAdmin()->create();
        [$product, $warehouse, $branch] = $this->stocked(1);

        $this->actingAs($admin)->post(route('admin.transfers.store'), [
            'code' => 'TR002',
            'branch_id' => $branch->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 4],
            ],
        ]);

        $transferId = \App\Models\StockTransfer::query()->where('code', 'TR002')->value('id');

        $this->actingAs($admin)
            ->post(route('admin.transfers.dispatch', $transferId))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('stock_transfers', [
            'id' => $transferId,
            'status' => 'draft',
        ]);
        $this->assertDatabaseHas('stocks', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 1,
        ]);
    }

    public function test_a_draft_cannot_be_received(): void
    {
        $admin = User::factory()->superAdmin()->create();
        [$product, , $branch] = $this->stocked(5);

        $this->actingAs($admin)->post(route('admin.transfers.store'), [
            'code' => 'TR003',
            'branch_id' => $branch->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $transferId = \App\Models\StockTransfer::query()->where('code', 'TR003')->value('id');

        $this->actingAs($admin)
            ->post(route('admin.transfers.receive', $transferId))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('stock_transfers', [
            'id' => $transferId,
            'status' => 'draft',
        ]);
    }

    /**
     * @return array{0: Product, 1: Warehouse, 2: Branch}
     */
    private function stocked(int $quantity): array
    {
        $state = State::query()->create([
            'state_name' => 'Andhra Pradesh',
            'status' => 'active',
        ]);
        $district = District::query()->create([
            'state_id' => $state->id,
            'district_name' => 'Krishna',
            'status' => 'active',
        ]);
        $city = City::query()->create([
            'state_id' => $state->id,
            'district_id' => $district->id,
            'city_name' => 'Vijayawada',
            'status' => 'active',
        ]);
        $warehouse = Warehouse::query()->create([
            'code' => 'WH001',
            'name' => 'Main Warehouse',
            'address_line' => 'Industrial Area',
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
            'pincode' => '520001',
            'status' => 'active',
        ]);
        $category = Category::query()->create([
            'name' => 'Sofas',
            'status' => 'active',
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'code' => 'SF001',
            'name' => 'Three Seater Sofa',
            'unit' => 'Piece',
            'selling_price' => 45000,
            'status' => 'active',
        ]);
        Stock::query()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => $quantity,
        ]);

        return [$product, $warehouse, $branch];
    }
}
