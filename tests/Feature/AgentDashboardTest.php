<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\Category;
use App\Models\City;
use App\Models\Customer;
use App\Models\District;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\State;
use App\Models\Stock;
use App\Models\StockRequest;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_is_limited_to_this_shop(): void
    {
        [$agent, $other, $product, $warehouse] = $this->shops();
        $manager = $agent->user;
        $cashier = User::factory()->agentStaff()->create(['agent_id' => $agent->id]);

        $this->sale($agent, $product, 45000, 2250, now());
        $this->sale($other, $product, 10000, 800, now());
        Stock::query()->create([
            'product_id' => $product->id,
            'agent_id' => $agent->id,
            'quantity' => 0,
        ]);
        StockTransfer::query()->create([
            'code' => 'TR001',
            'warehouse_id' => $warehouse->id,
            'agent_id' => $agent->id,
            'status' => StockTransfer::STATUS_DISPATCHED,
            'dispatched_at' => now(),
        ]);
        StockRequest::query()->create([
            'code' => 'R00004',
            'agent_id' => $agent->id,
            'status' => StockRequest::STATUS_REQUESTED,
        ]);

        $this->actingAs($manager)
            ->get(route('agent.home'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('45,000.00')
            ->assertSee('2,250.00')
            ->assertDontSee('10,000.00')
            ->assertSee('TR001')
            ->assertSee('R00004')
            ->assertSee('SF001')
            ->assertSee('Waiting To Receive');

        $this->actingAs($cashier)
            ->get(route('agent.home'))
            ->assertOk()
            ->assertSee('45,000.00')
            ->assertDontSee('10,000.00')
            ->assertDontSee('TR001')
            ->assertDontSee('R00004')
            ->assertDontSee('Waiting To Receive');
    }

    public function test_sales_report_uses_dates_commission_and_stays_on_this_shop(): void
    {
        [$agent, $other, $product] = $this->shops();
        $cashier = User::factory()->agentStaff()->create(['agent_id' => $agent->id]);

        $this->sale($agent, $product, 45000, 2250, now());
        $this->sale($agent, $product, 8000, 400, now()->subDays(10));
        $this->sale($other, $product, 10000, 800, now());

        $this->actingAs($cashier)
            ->get(route('agent.reports.sales-by-product', [
                'from' => now()->toDateString(),
                'to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('45,000.00')
            ->assertSee('2,250.00')
            ->assertDontSee('8,000.00')
            ->assertDontSee('10,000.00')
            ->assertSee('Commission');

        $this->actingAs($cashier)
            ->get(route('agent.reports.stock'))
            ->assertOk()
            ->assertSee('Stock On Hand');

        $this->actingAs($cashier)
            ->get(route('agent.reports.transfers'))
            ->assertForbidden();

        $this->actingAs($cashier)
            ->get(route('agent.reports.stock-requests'))
            ->assertForbidden();
    }

    public function test_a_manager_can_open_transfer_and_request_reports(): void
    {
        [$agent, , , $warehouse] = $this->shops();

        StockTransfer::query()->create([
            'code' => 'TR007',
            'warehouse_id' => $warehouse->id,
            'agent_id' => $agent->id,
            'status' => StockTransfer::STATUS_DISPATCHED,
            'dispatched_at' => now(),
        ]);
        StockRequest::query()->create([
            'code' => 'R00005',
            'agent_id' => $agent->id,
            'status' => StockRequest::STATUS_REQUESTED,
        ]);

        $this->actingAs($agent->user)
            ->get(route('agent.reports.transfers'))
            ->assertOk()
            ->assertSee('TR007');

        $this->actingAs($agent->user)
            ->get(route('agent.reports.stock-requests'))
            ->assertOk()
            ->assertSee('R00005');
    }

    /**
     * @return array{0: Agent, 1: Agent, 2: Product, 3: Warehouse}
     */
    private function shops(): array
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
        $category = Category::query()->create(['name' => 'Sofas', 'status' => 'active']);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'code' => 'SF001',
            'name' => 'Three Seater Sofa',
            'unit' => 'Piece',
            'selling_price' => 45000,
            'status' => 'active',
        ]);

        $agent = $this->agent('AG001', 'Sri Lakshmi Furniture Mart', 'manager.lakshmi@godwin.test', $state->id, $district->id, $city->id, $warehouse->id);
        $other = $this->agent('AG002', 'Kalanjali Home Studio', 'manager.kalanjali@godwin.test', $state->id, $district->id, $city->id, $warehouse->id);

        return [$agent, $other, $product, $warehouse];
    }

    private function agent(string $code, string $shop, string $email, int $stateId, int $districtId, int $cityId, int $warehouseId): Agent
    {
        $user = User::factory()->agent()->create([
            'name' => $shop,
            'email' => $email,
        ]);

        $agent = Agent::query()->create([
            'code' => $code,
            'name' => $shop,
            'user_id' => $user->id,
            'warehouse_id' => $warehouseId,
            'email' => $email,
            'address_line' => 'Main Road',
            'state_id' => $stateId,
            'district_id' => $districtId,
            'city_id' => $cityId,
            'pincode' => '520001',
            'status' => 'active',
        ]);
        $user->update(['agent_id' => $agent->id]);

        return $agent->fresh('user');
    }

    private function sale(Agent $agent, Product $product, float $total, float $commission, $at): void
    {
        $customer = Customer::query()->create([
            'name' => 'Walk In',
            'phone' => '9'.str_pad((string) (Customer::query()->count() + 1), 9, '0', STR_PAD_LEFT),
            'status' => 'active',
        ]);
        $order = Order::query()->create([
            'code' => 'S'.str_pad((string) (Order::query()->count() + 1), 5, '0', STR_PAD_LEFT),
            'channel' => Order::CHANNEL_AGENT,
            'customer_id' => $customer->id,
            'agent_id' => $agent->id,
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
            'commission_percent' => 5,
            'commission_amount' => $commission,
        ]);
    }
}
