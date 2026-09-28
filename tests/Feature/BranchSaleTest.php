<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\City;
use App\Models\Customer;
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

class BranchSaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_branch_staff_can_sell_stock_held_at_the_branch(): void
    {
        [$cashier, $product, $branch] = $this->counter(5);

        $this->actingAs($cashier)->post(route('branch.sales.store'), [
            'customer_name' => 'Ravi',
            'phone' => '9000000001',
            'payment_method' => 'cash',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('customers', [
            'phone' => '9000000001',
            'name' => 'Ravi',
        ]);
        $this->assertDatabaseHas('orders', [
            'branch_id' => $branch->id,
            'channel' => 'branch',
            'placed_by' => $cashier->id,
            'total' => 90000,
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('payments', [
            'method' => 'cash',
            'amount' => 90000,
            'status' => 'paid',
        ]);
        $this->assertDatabaseHas('stocks', [
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'quantity' => 3,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'type' => StockMovement::TYPE_SALE,
            'quantity_change' => -2,
            'quantity_after' => 3,
        ]);
    }

    public function test_the_same_phone_reuses_the_customer(): void
    {
        [$cashier, $product] = $this->counter(4);

        $this->actingAs($cashier)->post(route('branch.sales.store'), [
            'customer_name' => 'Ravi',
            'phone' => '9000000001',
            'payment_method' => 'upi',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $this->actingAs($cashier)->post(route('branch.sales.store'), [
            'customer_name' => 'Ravi Kumar',
            'phone' => '9000000001',
            'payment_method' => 'upi',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $this->assertSame(1, Customer::query()->where('phone', '9000000001')->count());
        $this->assertDatabaseHas('customers', [
            'phone' => '9000000001',
            'name' => 'Ravi Kumar',
        ]);
    }

    public function test_a_sale_is_rejected_when_the_branch_does_not_have_enough(): void
    {
        [$cashier, $product] = $this->counter(1);

        $this->actingAs($cashier)->post(route('branch.sales.store'), [
            'customer_name' => 'Ravi',
            'phone' => '9000000001',
            'payment_method' => 'cash',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
        ])->assertSessionHasErrors('items.0.quantity');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('stocks', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
    }

    public function test_branch_staff_cannot_open_the_admin_panel(): void
    {
        [$cashier] = $this->counter(1);

        $this->actingAs($cashier)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($cashier)->post(route('branch.logout'))->assertRedirect(route('branch.login'));
    }

    /**
     * @return array{0: User, 1: Product, 2: Branch}
     */
    private function counter(int $quantity): array
    {
        $role = Role::query()->create([
            'name' => 'Branch Staff',
            'slug' => Role::BRANCH_STAFF,
            'status' => 'active',
        ]);
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
        $cashier = User::factory()->create([
            'role_id' => $role->id,
            'status' => 'active',
        ]);
        Employee::query()->create([
            'user_id' => $cashier->id,
            'employee_code' => 'CAS001',
            'branch_id' => $branch->id,
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
            'branch_id' => $branch->id,
            'quantity' => $quantity,
        ]);

        return [$cashier, $product, $branch];
    }
}
