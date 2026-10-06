<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Customer;
use App\Models\District;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\State;
use App\Models\User;
use App\Models\Warehouse;
use App\Support\StoreCart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCatalog;
use Tests\TestCase;

class StorefrontCheckoutTest extends TestCase
{
    use CreatesCatalog;
    use RefreshDatabase;

    public function test_guest_is_redirected_from_checkout_to_login(): void
    {
        $product = $this->onlineProduct();
        app(StoreCart::class)->add($product, 1);

        $this->get(route('store.checkout'))
            ->assertRedirect(route('store.login'));
    }

    public function test_empty_bag_cannot_open_checkout(): void
    {
        $user = $this->storeCustomer();

        $this->actingAs($user)
            ->get(route('store.checkout'))
            ->assertRedirect(route('store.cart'));
    }

    public function test_signed_in_customer_can_place_an_online_cod_order(): void
    {
        $this->primaryWarehouse();
        $user = $this->storeCustomer([
            'name' => 'Ravi Kumar',
            'phone' => '9848011001',
            'email' => 'ravi@example.com',
        ]);
        $product = $this->onlineProduct();
        app(StoreCart::class)->add($product, 2);

        $this->actingAs($user)
            ->get(route('store.checkout'))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('Ravi Kumar')
            ->assertSee('Cash on Delivery');

        $this->actingAs($user)
            ->post(route('store.checkout.store'), [
                'address_source' => 'new',
                'shipping_address' => '12 MG Road',
                'shipping_city' => 'Vijayawada',
                'shipping_pincode' => '520010',
                'notes' => 'Call before delivery',
                'save_address' => '1',
            ])
            ->assertRedirect();

        $order = Order::query()->first();
        $this->assertNotNull($order);
        $this->assertSame(Order::CHANNEL_ONLINE, $order->channel);
        $this->assertSame(Order::STATUS_PLACED, $order->status);
        $this->assertSame($user->customer->id, $order->customer_id);
        $this->assertSame('12 MG Road', $order->shipping_address);
        $this->assertSame('Vijayawada', $order->shipping_city);
        $this->assertSame('520010', $order->shipping_pincode);
        $this->assertSame('Call before delivery', $order->notes);
        $this->assertTrue(str_starts_with($order->code, 'W'));
        $this->assertEquals(77998.0, (float) $order->total);

        $this->assertSame(1, $order->items()->count());
        $this->assertSame(2, (int) $order->items()->first()->quantity);
        $this->assertSame(Payment::METHOD_COD, $order->payment->method);
        $this->assertSame(Payment::STATUS_PENDING, $order->payment->status);
        $this->assertTrue(app(StoreCart::class)->isEmpty());
        $this->assertSame(1, $user->customer->addresses()->count());

        $this->actingAs($user)
            ->get(route('store.orders.show', $order))
            ->assertOk()
            ->assertSee($order->code)
            ->assertSee('Ravi Kumar')
            ->assertSee('12 MG Road')
            ->assertSee('Cash on Delivery');
    }

    public function test_registration_links_existing_walk_in_customer_by_phone(): void
    {
        Customer::query()->create([
            'name' => 'Walk In Buyer',
            'phone' => '9848011001',
            'email' => 'walkin@example.com',
            'status' => Customer::STATUS_ACTIVE,
        ]);

        $this->post(route('store.register.store'), [
            'name' => 'Ravi Kumar',
            'phone' => '9848011001',
            'email' => 'ravi@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ])->assertRedirect(route('store.home'));

        $this->assertAuthenticated();
        $this->assertSame(1, Customer::query()->where('phone', '9848011001')->count());
        $customer = Customer::query()->where('phone', '9848011001')->first();
        $this->assertSame(auth()->id(), $customer->user_id);
        $this->assertSame('Ravi Kumar', $customer->name);
        $this->assertSame('ravi@example.com', $customer->email);
    }

    public function test_inactive_customer_cannot_checkout(): void
    {
        $this->primaryWarehouse();
        $user = User::query()->create([
            'name' => 'Blocked Buyer',
            'email' => 'blocked@example.com',
            'phone' => '9848011999',
            'password' => 'Password1!',
            'role_id' => null,
            'status' => 'active',
        ]);
        Customer::query()->create([
            'user_id' => $user->id,
            'name' => 'Blocked Buyer',
            'phone' => '9848011999',
            'status' => Customer::STATUS_INACTIVE,
        ]);

        $product = $this->onlineProduct();
        app(StoreCart::class)->add($product, 1);

        $this->actingAs($user)
            ->get(route('store.checkout'))
            ->assertRedirect(route('store.cart'));

        $this->assertSame(0, Order::query()->count());
    }

    /**
     * @param  array{name?: string, phone?: string, email?: string}  $overrides
     */
    private function storeCustomer(array $overrides = []): User
    {
        $user = User::query()->create([
            'name' => $overrides['name'] ?? 'Store Customer',
            'email' => $overrides['email'] ?? 'customer@example.com',
            'phone' => $overrides['phone'] ?? '9848011000',
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
