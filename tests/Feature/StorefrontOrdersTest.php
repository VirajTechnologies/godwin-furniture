<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Customer;
use App\Models\District;
use App\Models\Order;
use App\Models\Product;
use App\Models\State;
use App\Models\User;
use App\Models\Warehouse;
use App\Support\StoreCart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCatalog;
use Tests\TestCase;

class StorefrontOrdersTest extends TestCase
{
    use CreatesCatalog;
    use RefreshDatabase;

    public function test_guest_cannot_view_orders(): void
    {
        $this->get(route('store.orders.index'))
            ->assertRedirect(route('store.login'));
    }

    public function test_customer_sees_only_their_online_orders(): void
    {
        $this->primaryWarehouse();
        $owner = $this->storeCustomer([
            'name' => 'Owner',
            'phone' => '9848011001',
            'email' => 'owner@example.com',
        ]);
        $other = $this->storeCustomer([
            'name' => 'Other',
            'phone' => '9848011002',
            'email' => 'other@example.com',
        ]);

        $product = $this->onlineProduct();
        app(StoreCart::class)->add($product, 1);

        $this->actingAs($owner)
            ->post(route('store.checkout.store'), [
                'address_source' => 'new',
                'shipping_address' => '12 MG Road',
                'shipping_city' => 'Vijayawada',
                'shipping_pincode' => '520010',
                'save_address' => '1',
            ])
            ->assertRedirect();

        $mine = Order::query()->first();
        $this->assertNotNull($mine);

        Order::query()->create([
            'code' => 'W99999',
            'channel' => Order::CHANNEL_ONLINE,
            'customer_id' => $other->customer->id,
            'warehouse_id' => $mine->warehouse_id,
            'total' => 1000,
            'status' => Order::STATUS_PLACED,
            'shipping_address' => 'Other street',
            'shipping_city' => 'Guntur',
            'shipping_pincode' => '522001',
        ]);

        $this->actingAs($owner)
            ->get(route('store.orders.index'))
            ->assertOk()
            ->assertSee($mine->code)
            ->assertDontSee('W99999');

        $this->actingAs($owner)
            ->get(route('store.orders.show', $mine))
            ->assertOk()
            ->assertSee($mine->code)
            ->assertSee('12 MG Road');

        $this->actingAs($owner)
            ->get(route('store.orders.show', 'W99999'))
            ->assertNotFound();
    }

    public function test_empty_orders_page_shows_prompt(): void
    {
        $user = $this->storeCustomer();

        $this->actingAs($user)
            ->get(route('store.orders.index'))
            ->assertOk()
            ->assertSee('No orders yet');
    }

    /**
     * @param  array{name?: string, phone?: string, email?: string}  $overrides
     */
    private function storeCustomer(array $overrides = []): User
    {
        $user = User::query()->create([
            'name' => $overrides['name'] ?? 'Store Customer',
            'email' => $overrides['email'] ?? 'customer'.uniqid().'@example.com',
            'phone' => $overrides['phone'] ?? ('98480'.str_pad((string) random_int(10000, 99999), 5, '0', STR_PAD_LEFT)),
            'password' => 'Password1!',
            'role_id' => null,
            'status' => 'active',
        ]);

        Customer::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'status' => Customer::STATUS_ACTIVE,
        ]);

        return $user->fresh(['customer']);
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
