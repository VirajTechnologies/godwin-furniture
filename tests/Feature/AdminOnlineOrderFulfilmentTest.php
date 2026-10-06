<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Customer;
use App\Models\District;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\State;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use App\Support\StoreCart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCatalog;
use Tests\TestCase;

class AdminOnlineOrderFulfilmentTest extends TestCase
{
    use CreatesCatalog;
    use RefreshDatabase;

    public function test_admin_can_confirm_dispatch_and_complete_an_online_order(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $warehouse = $this->primaryWarehouse();
        $product = $this->onlineProduct();

        Stock::query()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 5,
        ]);

        $customer = Customer::query()->create([
            'name' => 'Ravi Kumar',
            'phone' => '9848011001',
            'email' => 'ravi@example.com',
            'status' => Customer::STATUS_ACTIVE,
        ]);

        $user = User::query()->create([
            'name' => 'Ravi Kumar',
            'email' => 'ravi@example.com',
            'phone' => '9848011001',
            'password' => 'Password1!',
            'status' => 'active',
        ]);
        $customer->update(['user_id' => $user->id]);

        app(StoreCart::class)->add($product, 2);

        $this->actingAs($user)->post(route('store.checkout.store'), [
            'address_source' => 'new',
            'shipping_address' => '12 MG Road',
            'shipping_city' => 'Vijayawada',
            'shipping_pincode' => '520010',
            'save_address' => '0',
        ])->assertRedirect();

        $order = Order::query()->first();
        $this->assertNotNull($order);
        $this->assertSame(Order::STATUS_PLACED, $order->status);

        $this->actingAs($admin)
            ->post(route('admin.orders.confirm', $order))
            ->assertRedirect(route('admin.orders.show', $order));

        $this->assertSame(Order::STATUS_CONFIRMED, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->confirmed_at);
        $this->assertSame(5, (int) Stock::query()->where('product_id', $product->id)->value('quantity'));

        $this->actingAs($admin)
            ->post(route('admin.orders.dispatch', $order))
            ->assertRedirect(route('admin.orders.show', $order));

        $this->assertSame(Order::STATUS_DISPATCHED, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->dispatched_at);
        $this->assertSame(3, (int) Stock::query()->where('product_id', $product->id)->value('quantity'));
        $this->assertDatabaseHas('stock_movements', [
            'type' => StockMovement::TYPE_SALE,
            'quantity_change' => -2,
            'quantity_after' => 3,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.orders.complete', $order))
            ->assertRedirect(route('admin.orders.show', $order));

        $order->refresh();
        $this->assertSame(Order::STATUS_COMPLETED, $order->status);
        $this->assertNotNull($order->completed_at);
        $this->assertSame(Payment::STATUS_PAID, $order->payment->status);

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Order Progress')
            ->assertSee('Placed')
            ->assertSee('Confirmed')
            ->assertSee('Dispatched')
            ->assertSee('Completed');

        $this->actingAs($user)
            ->get(route('store.orders.show', $order))
            ->assertOk()
            ->assertSee('Order Progress')
            ->assertSee('Confirmed')
            ->assertSee('Dispatched');
    }

    public function test_dispatch_fails_when_warehouse_stock_is_insufficient(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $warehouse = $this->primaryWarehouse();
        $product = $this->onlineProduct();

        Stock::query()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 1,
        ]);

        $customer = Customer::query()->create([
            'name' => 'Ravi Kumar',
            'phone' => '9848011001',
            'email' => 'ravi@example.com',
            'status' => Customer::STATUS_ACTIVE,
        ]);
        $user = User::query()->create([
            'name' => 'Ravi Kumar',
            'email' => 'ravi@example.com',
            'phone' => '9848011001',
            'password' => 'Password1!',
            'status' => 'active',
        ]);
        $customer->update(['user_id' => $user->id]);

        app(StoreCart::class)->add($product, 2);
        $this->actingAs($user)->post(route('store.checkout.store'), [
            'address_source' => 'new',
            'shipping_address' => '12 MG Road',
            'shipping_city' => 'Vijayawada',
            'shipping_pincode' => '520010',
            'save_address' => '0',
        ]);

        $order = Order::query()->first();
        $order->update(['status' => Order::STATUS_CONFIRMED]);

        $this->actingAs($admin)
            ->from(route('admin.orders.show', $order))
            ->post(route('admin.orders.dispatch', $order))
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHas('error');

        $this->assertSame(Order::STATUS_CONFIRMED, $order->fresh()->status);
        $this->assertSame(1, (int) Stock::query()->where('product_id', $product->id)->value('quantity'));
    }

    private function onlineProduct(): Product
    {
        $category = $this->subcategory();

        return Product::query()->create([
            'category_id' => $category->id,
            'code' => 'SF001',
            'slug' => 'alanis-sofa',
            'name' => 'Alanis Metal Frame Sofa',
            'unit' => 'Piece',
            'selling_price' => 42000,
            'online_price' => 38999,
            'compare_at_price' => 54999,
            'is_online' => true,
            'status' => 'active',
        ]);
    }

    private function primaryWarehouse(): Warehouse
    {
        $state = State::query()->create(['state_name' => 'Andhra Pradesh', 'status' => 'active']);
        $district = District::query()->create(['state_id' => $state->id, 'district_name' => 'Krishna', 'status' => 'active']);
        $city = City::query()->create([
            'state_id' => $state->id,
            'district_id' => $district->id,
            'city_name' => 'Vijayawada',
            'status' => 'active',
        ]);

        return Warehouse::query()->create([
            'code' => 'WH001',
            'name' => 'Main Warehouse',
            'address_line' => 'Industrial Area',
            'state_id' => $state->id,
            'district_id' => $district->id,
            'city_id' => $city->id,
            'pincode' => '520001',
            'status' => 'active',
        ]);
    }
}
