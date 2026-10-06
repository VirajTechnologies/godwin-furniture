<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\City;
use App\Models\Customer;
use App\Models\District;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\State;
use App\Models\StockRequest;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_todays_sales_and_waiting_work(): void
    {
        $admin = User::factory()->superAdmin()->create();
        [$branch, $product, $warehouse] = $this->showroom();

        $this->sale($branch, $product, 45000, now());
        $this->sale($branch, $product, 10000, now()->subDay());

        StockTransfer::query()->create([
            'code' => 'TR001',
            'warehouse_id' => $warehouse->id,
            'branch_id' => $branch->id,
            'status' => StockTransfer::STATUS_DISPATCHED,
            'dispatched_at' => now()->subHour(),
        ]);

        StockRequest::query()->create([
            'code' => 'R00004',
            'branch_id' => $branch->id,
            'status' => StockRequest::STATUS_REQUESTED,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('45,000.00')
            ->assertSee('Vijayawada Showroom')
            ->assertSee('TR001')
            ->assertSee('R00004');
    }

    public function test_sales_by_branch_uses_the_selected_dates(): void
    {
        $admin = User::factory()->superAdmin()->create();
        [$branch, $product] = $this->showroom();

        $this->sale($branch, $product, 45000, now());
        $this->sale($branch, $product, 10000, now()->subDays(10));

        $this->actingAs($admin)
            ->get(route('admin.reports.sales-by-branch', [
                'from' => now()->toDateString(),
                'to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('45,000.00')
            ->assertDontSee('10,000.00');
    }

    public function test_branch_staff_cannot_open_reports(): void
    {
        $staff = User::factory()->branchStaff()->create();

        $this->actingAs($staff)
            ->get(route('admin.reports.sales-by-branch'))
            ->assertForbidden();
    }

    /**
     * @return array{0: Branch, 1: Product, 2: Warehouse}
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

        return [$branch, $product, $warehouse];
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
