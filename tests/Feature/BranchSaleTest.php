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
            'delivery_type' => 'pickup',
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
            'delivery_type' => 'pickup',
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
            'delivery_type' => 'pickup',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $this->actingAs($cashier)->post(route('branch.sales.store'), [
            'customer_name' => 'Ravi Kumar',
            'phone' => '9000000001',
            'payment_method' => 'upi',
            'delivery_type' => 'pickup',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $this->assertSame(1, Customer::query()->where('phone', '9000000001')->count());
        $this->assertDatabaseHas('customers', [
            'phone' => '9000000001',
            'name' => 'Ravi',
        ]);
    }

    public function test_branch_staff_can_record_a_door_delivery_sale(): void
    {
        [$cashier, $product, $branch] = $this->counter(3);

        $this->actingAs($cashier)->post(route('branch.sales.store'), [
            'customer_name' => 'Sita',
            'phone' => '9000000011',
            'payment_method' => 'cash',
            'delivery_type' => 'delivery',
            'shipping_address' => '12 MG Road',
            'shipping_city' => 'Vijayawada',
            'shipping_pincode' => '520001',
            'notes' => 'Call before delivery',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'branch_id' => $branch->id,
            'channel' => 'branch',
            'delivery_type' => 'delivery',
            'shipping_address' => '12 MG Road',
            'shipping_city' => 'Vijayawada',
            'shipping_pincode' => '520001',
            'notes' => 'Call before delivery',
            'status' => 'completed',
        ]);
    }

    public function test_door_delivery_requires_an_address(): void
    {
        [$cashier, $product] = $this->counter(2);

        $this->actingAs($cashier)->post(route('branch.sales.store'), [
            'customer_name' => 'Sita',
            'phone' => '9000000012',
            'payment_method' => 'cash',
            'delivery_type' => 'delivery',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ])->assertSessionHasErrors(['shipping_address', 'shipping_city', 'shipping_pincode']);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_leaving_the_phone_field_can_load_an_existing_customer(): void
    {
        [$cashier] = $this->counter(1);
        Customer::query()->create([
            'name' => 'Ramesh',
            'phone' => '9000000001',
            'email' => 'ramesh@example.com',
            'status' => 'active',
        ]);
        Customer::query()->create([
            'name' => 'Inactive Buyer',
            'phone' => '9000000002',
            'status' => 'inactive',
        ]);

        $this->actingAs($cashier)
            ->get(route('branch.customers.lookup', ['phone' => '9000000001']))
            ->assertOk()
            ->assertJson([
                'found' => true,
                'name' => 'Ramesh',
                'email' => 'ramesh@example.com',
                'active' => true,
            ]);

        $this->actingAs($cashier)
            ->get(route('branch.customers.lookup', ['phone' => '9000000002']))
            ->assertOk()
            ->assertJson([
                'found' => true,
                'name' => 'Inactive Buyer',
                'active' => false,
            ]);

        $this->actingAs($cashier)
            ->get(route('branch.customers.lookup', ['phone' => '9000000099']))
            ->assertOk()
            ->assertJson(['found' => false]);

        $this->actingAs($cashier)
            ->get(route('branch.sales.create'))
            ->assertOk()
            ->assertSee('After 10 digits, the name and email fill in', false);
    }

    public function test_a_sale_is_rejected_when_the_branch_does_not_have_enough(): void
    {
        [$cashier, $product] = $this->counter(1);

        $this->actingAs($cashier)->post(route('branch.sales.store'), [
            'customer_name' => 'Ravi',
            'phone' => '9000000001',
            'payment_method' => 'cash',
            'delivery_type' => 'pickup',
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
