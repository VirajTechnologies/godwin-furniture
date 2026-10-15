<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\AgentProductTerm;
use App\Models\Category;
use App\Models\City;
use App\Models\District;
use App\Models\Product;
use App\Models\State;
use App\Models\Stock;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentSaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_agent_sale_keeps_the_price_inside_that_shops_band_and_stores_commission(): void
    {
        [$agent, $other, $product] = $this->shops();

        Stock::query()->create([
            'product_id' => $product->id,
            'agent_id' => $agent->id,
            'quantity' => 2,
        ]);

        $this->actingAs($agent->user)
            ->post(route('agent.sales.store'), [
                'customer_name' => 'Meena Rao',
                'phone' => '9848012001',
                'method' => 'cash',
                'delivery_type' => 'pickup',
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 39000],
                ],
            ])
            ->assertSessionHas('error');

        $this->actingAs($agent->user)
            ->post(route('agent.sales.store'), [
                'customer_name' => 'Meena Rao',
                'phone' => '9848012001',
                'method' => 'cash',
                'delivery_type' => 'pickup',
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 45000],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'agent_id' => $agent->id,
            'delivery_type' => 'pickup',
        ]);

        $this->assertDatabaseHas('stocks', [
            'product_id' => $product->id,
            'agent_id' => $agent->id,
            'quantity' => 1,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'unit_price' => 45000,
            'commission_percent' => 5,
            'commission_amount' => 2250,
        ]);

        $orderId = \App\Models\Order::query()->where('agent_id', $agent->id)->value('id');

        $this->actingAs($other->user)
            ->get(route('agent.sales.show', $orderId))
            ->assertNotFound();
    }

    public function test_an_agent_receives_a_transfer_and_can_sell_only_that_stock(): void
    {
        $admin = User::factory()->superAdmin()->create();
        [$agent, , $product, $warehouse] = $this->shops(withWarehouse: true);

        Stock::query()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 4,
        ]);

        $this->actingAs($admin)->post(route('admin.transfers.store'), [
            'code' => 'TR010',
            'destination' => 'agent',
            'agent_id' => $agent->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 4],
            ],
        ])->assertRedirect();

        $transferId = StockTransfer::query()->where('code', 'TR010')->value('id');

        $this->actingAs($admin)->post(route('admin.transfers.dispatch', $transferId), [
            'destination' => 'agent',
            'agent_id' => $agent->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 4],
            ],
        ])->assertRedirect();

        $itemId = (int) \App\Models\StockTransferItem::query()->where('stock_transfer_id', $transferId)->value('id');

        $this->actingAs($agent->user)->post(route('agent.transfers.complete', $transferId), [
            'return_reason' => 'One piece was damaged in transit.',
            'items' => [
                [
                    'id' => $itemId,
                    'received_quantity' => 3,
                    'returned_quantity' => 1,
                ],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('stocks', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 1,
        ]);
        $this->assertDatabaseHas('stocks', [
            'product_id' => $product->id,
            'agent_id' => $agent->id,
            'quantity' => 3,
        ]);
        $this->assertDatabaseHas('stock_transfers', [
            'id' => $transferId,
            'status' => 'partial',
        ]);

        $this->actingAs($agent->user)->post(route('agent.sales.store'), [
            'customer_name' => 'Meena Rao',
            'phone' => '9848012001',
            'method' => 'cash',
            'delivery_type' => 'pickup',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 45000],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('stocks', [
            'product_id' => $product->id,
            'agent_id' => $agent->id,
            'quantity' => 2,
        ]);
    }

    public function test_an_agent_can_sell_several_products_and_deliver_them(): void
    {
        [$agent, , $product] = $this->shops();
        $chair = Product::query()->create([
            'category_id' => $product->category_id,
            'code' => 'CH001',
            'name' => 'Dining Chair',
            'unit' => 'Piece',
            'selling_price' => 12000,
            'compare_at_price' => 15999,
            'status' => 'active',
        ]);
        AgentProductTerm::query()->create([
            'agent_id' => $agent->id,
            'product_id' => $chair->id,
            'minimum_price' => 12000,
            'commission_percent' => 8,
        ]);
        Stock::query()->create([
            'product_id' => $product->id,
            'agent_id' => $agent->id,
            'quantity' => 2,
        ]);
        Stock::query()->create([
            'product_id' => $chair->id,
            'agent_id' => $agent->id,
            'quantity' => 4,
        ]);

        $this->actingAs($agent->user)
            ->get(route('agent.sales.create'))
            ->assertOk()
            ->assertSee('Add Line')
            ->assertSee('Door delivery')
            ->assertSee('Collect at store');

        $this->actingAs($agent->user)
            ->post(route('agent.sales.store'), [
                'customer_name' => 'Meena Rao',
                'phone' => '9848012001',
                'method' => 'upi',
                'delivery_type' => 'delivery',
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 45000],
                    ['product_id' => $chair->id, 'quantity' => 2, 'unit_price' => 14000],
                ],
            ])
            ->assertSessionHasErrors(['shipping_address', 'shipping_city', 'shipping_pincode']);

        $this->actingAs($agent->user)
            ->post(route('agent.sales.store'), [
                'customer_name' => 'Meena Rao',
                'phone' => '9848012001',
                'method' => 'upi',
                'delivery_type' => 'delivery',
                'shipping_address' => '12 Besant Road',
                'shipping_city' => 'Vijayawada',
                'shipping_pincode' => '520002',
                'notes' => 'Call before delivery',
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 45000],
                    ['product_id' => $chair->id, 'quantity' => 2, 'unit_price' => 14000],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'agent_id' => $agent->id,
            'channel' => 'agent',
            'delivery_type' => 'delivery',
            'shipping_address' => '12 Besant Road',
            'shipping_city' => 'Vijayawada',
            'shipping_pincode' => '520002',
            'notes' => 'Call before delivery',
            'total' => 73000,
        ]);
        $this->assertDatabaseHas('order_items', [
            'product_id' => $chair->id,
            'quantity' => 2,
            'commission_percent' => 8,
            'commission_amount' => 2240,
        ]);
        $this->assertDatabaseHas('stocks', [
            'product_id' => $product->id,
            'agent_id' => $agent->id,
            'quantity' => 1,
        ]);
        $this->assertDatabaseHas('stocks', [
            'product_id' => $chair->id,
            'agent_id' => $agent->id,
            'quantity' => 2,
        ]);
    }

    /**
     * @return array{0: Agent, 1: Agent, 2: Product, 3: Warehouse|null}
     */
    private function shops(bool $withWarehouse = false): array
    {
        $state = State::query()->create(['state_name' => 'Andhra Pradesh', 'status' => 'active']);
        $district = District::query()->create(['state_id' => $state->id, 'district_name' => 'Krishna', 'status' => 'active']);
        $city = City::query()->create(['state_id' => $state->id, 'district_id' => $district->id, 'city_name' => 'Vijayawada', 'status' => 'active']);
        $warehouse = $withWarehouse ? Warehouse::query()->create([
            'code' => 'WH001',
            'name' => 'Main Warehouse',
            'address_line' => 'Industrial Area',
            'state_id' => $state->id,
            'district_id' => $district->id,
            'city_id' => $city->id,
            'pincode' => '520001',
            'status' => 'active',
        ]) : null;
        $category = Category::query()->create(['name' => 'Sofas', 'status' => 'active']);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'code' => 'SF001',
            'name' => 'Three Seater Sofa',
            'unit' => 'Piece',
            'selling_price' => 42000,
            'compare_at_price' => 54999,
            'status' => 'active',
        ]);

        $agent = $this->agent('AG001', 'Sri Lakshmi Furniture Mart', 'lakshmi.mart@godwin.test', $state->id, $district->id, $city->id, $warehouse?->id);
        $other = $this->agent('AG002', 'Kalanjali Home Studio', 'kalanjali@godwin.test', $state->id, $district->id, $city->id, $warehouse?->id);

        AgentProductTerm::query()->create([
            'agent_id' => $agent->id,
            'product_id' => $product->id,
            'minimum_price' => 40000,
            'commission_percent' => 5,
        ]);
        AgentProductTerm::query()->create([
            'agent_id' => $other->id,
            'product_id' => $product->id,
            'minimum_price' => 43000,
            'commission_percent' => 8,
        ]);

        return [$agent, $other, $product, $warehouse];
    }

    private function agent(string $code, string $shop, string $email, int $stateId, int $districtId, int $cityId, ?int $warehouseId = null): Agent
    {
        $user = User::factory()->agent()->create([
            'name' => $shop,
            'email' => $email,
        ]);

        return Agent::query()->create([
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
    }
}
