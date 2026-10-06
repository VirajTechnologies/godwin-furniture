<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\City;
use App\Models\District;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Role;
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
            ->post(route('admin.transfers.dispatch', $transferId), [
                'branch_id' => $branch->id,
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 2],
                ],
            ])
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

        $stockId = Stock::query()->where('warehouse_id', $warehouse->id)->value('id');

        $this->actingAs($admin)
            ->get(route('admin.stocks.edit', $stockId))
            ->assertOk()
            ->assertSee('Vijayawada Showroom')
            ->assertSee(route('admin.transfers.edit', $transferId), false);
        $this->assertDatabaseMissing('stocks', [
            'product_id' => $product->id,
            'branch_id' => $branch->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.transfers.edit', $transferId))
            ->assertOk()
            ->assertSee('The branch manager receives this transfer after counting the pieces at the showroom.')
            ->assertDontSee('Branch stock will increase.');

        $manager = $this->branchUser($branch, Role::BRANCH_MANAGER, 'Branch Manager', 'MGR001');

        $this->actingAs($manager)
            ->post(route('branch.transfers.receive', $transferId))
            ->assertRedirect(route('branch.transfers.show', $transferId));

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

        $this->actingAs($admin)
            ->get(route('admin.transfers.index'))
            ->assertOk()
            ->assertSee('>Created<', false)
            ->assertSee('>Dispatched<', false)
            ->assertSee('>Received<', false);

        $this->actingAs($admin)
            ->get(route('admin.transfers.edit', $transferId))
            ->assertOk()
            ->assertSee('Dispatched')
            ->assertSee('Received');
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
            ->post(route('admin.transfers.dispatch', $transferId), [
                'branch_id' => $branch->id,
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 4],
                ],
            ])
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

        $manager = $this->branchUser($branch, Role::BRANCH_MANAGER, 'Branch Manager', 'MGR001');

        $this->actingAs($manager)
            ->post(route('branch.transfers.receive', $transferId))
            ->assertNotFound();

        $this->actingAs($admin)
            ->post('/admin/transfers/'.$transferId.'/receive')
            ->assertNotFound();

        $this->assertDatabaseHas('stock_transfers', [
            'id' => $transferId,
            'status' => 'draft',
        ]);
    }

    public function test_a_cashier_cannot_receive_a_transfer(): void
    {
        $admin = User::factory()->superAdmin()->create();
        [$product, , $branch] = $this->stocked(5);

        $this->actingAs($admin)->post(route('admin.transfers.store'), [
            'code' => 'TR004',
            'branch_id' => $branch->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $transferId = \App\Models\StockTransfer::query()->where('code', 'TR004')->value('id');
        $this->actingAs($admin)->post(route('admin.transfers.dispatch', $transferId), [
            'branch_id' => $branch->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $cashier = $this->branchUser($branch, Role::BRANCH_STAFF, 'Branch Staff', 'CSH001');

        $this->actingAs($cashier)->get(route('branch.transfers.index'))->assertForbidden();
        $this->actingAs($cashier)->post(route('branch.transfers.receive', $transferId))->assertForbidden();

        $this->assertDatabaseHas('stock_transfers', [
            'id' => $transferId,
            'status' => 'dispatched',
        ]);
    }

    public function test_a_manager_cannot_receive_another_branch_transfer(): void
    {
        $admin = User::factory()->superAdmin()->create();
        [$product, $warehouse, $branch] = $this->stocked(5);

        $other = Branch::query()->create([
            'code' => 'BR002',
            'name' => 'Guntur Showroom',
            'warehouse_id' => $warehouse->id,
            'address_line' => 'Brodipet',
            'state_id' => $branch->state_id,
            'district_id' => $branch->district_id,
            'city_id' => $branch->city_id,
            'pincode' => '522002',
            'status' => 'active',
        ]);
        $manager = $this->branchUser($other, Role::BRANCH_MANAGER, 'Branch Manager', 'MGR002');

        $this->actingAs($admin)->post(route('admin.transfers.store'), [
            'code' => 'TR005',
            'branch_id' => $branch->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $transferId = \App\Models\StockTransfer::query()->where('code', 'TR005')->value('id');
        $this->actingAs($admin)->post(route('admin.transfers.dispatch', $transferId), [
            'branch_id' => $branch->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $this->actingAs($manager)
            ->post(route('branch.transfers.receive', $transferId))
            ->assertNotFound();

        $this->assertDatabaseHas('stock_transfers', [
            'id' => $transferId,
            'status' => 'dispatched',
        ]);
    }

    public function test_dispatch_saves_the_current_form_lines_before_reducing_stock(): void
    {
        $admin = User::factory()->superAdmin()->create();
        [$product, $warehouse, $branch] = $this->stocked(10);

        $this->actingAs($admin)->post(route('admin.transfers.store'), [
            'code' => 'TR006',
            'branch_id' => $branch->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $transferId = \App\Models\StockTransfer::query()->where('code', 'TR006')->value('id');

        $this->actingAs($admin)
            ->post(route('admin.transfers.dispatch', $transferId), [
                'branch_id' => $branch->id,
                'notes' => 'Updated on dispatch',
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 4],
                ],
            ])
            ->assertRedirect(route('admin.transfers.edit', $transferId));

        $this->assertDatabaseHas('stock_transfer_items', [
            'stock_transfer_id' => $transferId,
            'product_id' => $product->id,
            'quantity' => 4,
        ]);
        $this->assertDatabaseHas('stock_transfers', [
            'id' => $transferId,
            'status' => 'dispatched',
            'notes' => 'Updated on dispatch',
        ]);
        $this->assertDatabaseHas('stocks', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 6,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'type' => StockMovement::TYPE_TRANSFER_OUT,
            'quantity_change' => -4,
            'quantity_after' => 6,
        ]);
    }

    private function branchUser(Branch $branch, string $slug, string $name, string $code): User
    {
        $role = Role::query()->firstOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'status' => 'active'],
        );
        $user = User::factory()->create([
            'role_id' => $role->id,
            'status' => 'active',
        ]);
        Employee::query()->create([
            'user_id' => $user->id,
            'employee_code' => $code,
            'branch_id' => $branch->id,
            'designation' => $name,
            'status' => 'active',
        ]);

        return $user;
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
