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
use App\Models\StockRequest;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_manager_can_ask_for_stock_without_changing_quantity(): void
    {
        [$manager, $product, $branch] = $this->showroom();

        $this->actingAs($manager)->post(route('branch.stock-requests.store'), [
            'notes' => 'Need sofas for the weekend',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ])->assertRedirect();

        $request = StockRequest::query()->first();
        $this->assertNotNull($request);
        $this->assertSame($branch->id, $request->branch_id);
        $this->assertSame('requested', $request->status);
        $this->assertNull($request->stock_transfer_id);
        $this->assertDatabaseHas('stock_request_items', [
            'stock_request_id' => $request->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('stocks', [
            'product_id' => $product->id,
            'warehouse_id' => $branch->warehouse_id,
            'quantity' => 8,
        ]);
        $this->assertDatabaseMissing('stocks', [
            'product_id' => $product->id,
            'branch_id' => $branch->id,
        ]);
    }

    public function test_a_cashier_cannot_ask_for_stock(): void
    {
        [, $product, $branch] = $this->showroom();
        $cashier = $this->person($branch, Role::BRANCH_STAFF, 'Branch Staff', 'CSH001');

        $this->actingAs($cashier)->get(route('branch.stock-requests.index'))->assertForbidden();
        $this->actingAs($cashier)->post(route('branch.stock-requests.store'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ])->assertForbidden();
    }

    public function test_head_office_turns_a_request_into_a_draft_transfer(): void
    {
        [$manager, $product, $branch] = $this->showroom();
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($manager)->post(route('branch.stock-requests.store'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $request = StockRequest::query()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.stock-requests.transfer', $request))
            ->assertRedirect();

        $request->refresh();
        $this->assertNotNull($request->stock_transfer_id);
        $this->assertDatabaseHas('stock_transfers', [
            'id' => $request->stock_transfer_id,
            'branch_id' => $branch->id,
            'warehouse_id' => $branch->warehouse_id,
            'status' => 'draft',
        ]);
        $this->assertDatabaseHas('stock_transfer_items', [
            'stock_transfer_id' => $request->stock_transfer_id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('stocks', [
            'product_id' => $product->id,
            'warehouse_id' => $branch->warehouse_id,
            'quantity' => 8,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.stock-requests.transfer', $request))
            ->assertRedirect(route('admin.transfers.edit', $request->stock_transfer_id));

        $this->assertSame(1, \App\Models\StockTransfer::query()->count());
    }

    /**
     * @return array{0: User, 1: Product, 2: Branch}
     */
    private function showroom(): array
    {
        $state = State::query()->create(['state_name' => 'Andhra Pradesh', 'status' => 'active']);
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
            'pincode' => '520010',
            'status' => 'active',
        ]);
        $category = Category::query()->create(['name' => 'Sofas', 'status' => 'active']);
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
            'quantity' => 8,
        ]);

        return [$this->person($branch, Role::BRANCH_MANAGER, 'Branch Manager', 'MGR001'), $product, $branch];
    }

    private function person(Branch $branch, string $slug, string $name, string $code): User
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
}
