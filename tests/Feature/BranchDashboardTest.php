<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\City;
use App\Models\Customer;
use App\Models\District;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Role;
use App\Models\State;
use App\Models\Stock;
use App\Models\StockRequest;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_is_limited_to_this_branch(): void
    {
        [$branch, $other, $product] = $this->showrooms();
        $manager = $this->branchUser($branch, Role::BRANCH_MANAGER, 'Branch Manager', 'EMP001');
        $cashier = $this->branchUser($branch, Role::BRANCH_STAFF, 'Branch Staff', 'EMP002');

        $this->sale($branch, $product, 45000, now());
        $this->sale($other, $product, 10000, now());
        Stock::query()->create([
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'quantity' => 0,
        ]);
        StockTransfer::query()->create([
            'code' => 'TR001',
            'warehouse_id' => $branch->warehouse_id,
            'branch_id' => $branch->id,
            'status' => StockTransfer::STATUS_DISPATCHED,
            'dispatched_at' => now(),
        ]);
        StockRequest::query()->create([
            'code' => 'R00004',
            'branch_id' => $branch->id,
            'status' => StockRequest::STATUS_REQUESTED,
        ]);

        $this->actingAs($manager)
            ->get(route('branch.home'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('45,000.00')
            ->assertDontSee('10,000.00')
            ->assertSee('TR001')
            ->assertSee('R00004')
            ->assertSee('SF001');

        $this->actingAs($cashier)
            ->get(route('branch.home'))
            ->assertOk()
            ->assertSee('45,000.00')
            ->assertDontSee('10,000.00')
            ->assertDontSee('TR001')
            ->assertDontSee('R00004');
    }

    public function test_sales_report_uses_dates_and_stays_on_this_branch(): void
    {
        [$branch, $other, $product] = $this->showrooms();
        $cashier = $this->branchUser($branch, Role::BRANCH_STAFF, 'Branch Staff', 'EMP002');

        $this->sale($branch, $product, 45000, now());
        $this->sale($branch, $product, 8000, now()->subDays(10));
        $this->sale($other, $product, 10000, now());

        $this->actingAs($cashier)
            ->get(route('branch.reports.sales-by-product', [
                'from' => now()->toDateString(),
                'to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('45,000.00')
            ->assertDontSee('8,000.00')
            ->assertDontSee('10,000.00');

        $this->actingAs($cashier)
            ->get(route('branch.reports.transfers'))
            ->assertForbidden();
    }

    /**
     * @return array{0: Branch, 1: Branch, 2: Product}
     */
    private function showrooms(): array
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
        $other = Branch::query()->create([
            'code' => 'BR002',
            'name' => 'Guntur Showroom',
            'warehouse_id' => $warehouse->id,
            'address_line' => 'Main Road',
            'state_id' => $state->id,
            'district_id' => $district->id,
            'city_id' => $city->id,
            'pincode' => '522001',
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

        return [$branch, $other, $product];
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

    private function sale(Branch $branch, Product $product, float $total, $at): void
    {
        $customer = Customer::query()->create([
            'name' => 'Walk In',
            'phone' => '9'.str_pad((string) (Customer::query()->count() + 1), 9, '0', STR_PAD_LEFT),
            'status' => 'active',
        ]);
        $order = Order::query()->create([
            'code' => 'S'.str_pad((string) (Order::query()->count() + 1), 5, '0', STR_PAD_LEFT),
            'channel' => Order::CHANNEL_BRANCH,
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'total' => $total,
            'status' => Order::STATUS_COMPLETED,
        ]);
        $order->forceFill(['created_at' => $at, 'updated_at' => $at])->save();
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $total,
            'line_total' => $total,
        ]);
    }
}
