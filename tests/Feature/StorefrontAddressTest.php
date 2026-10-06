<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Customer;
use App\Models\CustomerAddress;
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

class StorefrontAddressTest extends TestCase
{
    use CreatesCatalog;
    use RefreshDatabase;

    public function test_customer_can_manage_addresses_on_account_page(): void
    {
        $user = $this->storeCustomer();

        $this->actingAs($user)
            ->post(route('store.account.addresses.store'), [
                'label' => 'Home',
                'address_line' => '12 MG Road',
                'city' => 'Vijayawada',
                'pincode' => '520010',
                'is_default' => '1',
            ])
            ->assertRedirect(route('store.account'));

        $home = CustomerAddress::query()->first();
        $this->assertNotNull($home);
        $this->assertTrue($home->is_default);

        $this->actingAs($user)
            ->post(route('store.account.addresses.store'), [
                'label' => 'Office',
                'address_line' => '45 Benz Circle',
                'city' => 'Vijayawada',
                'pincode' => '520010',
            ])
            ->assertRedirect(route('store.account'));

        $office = CustomerAddress::query()->where('label', 'Office')->first();
        $this->assertNotNull($office);

        $this->actingAs($user)
            ->put(route('store.account.addresses.default', $office))
            ->assertRedirect(route('store.account'));

        $this->assertTrue($office->fresh()->is_default);
        $this->assertFalse($home->fresh()->is_default);

        $this->actingAs($user)
            ->delete(route('store.account.addresses.destroy', $office))
            ->assertRedirect(route('store.account'));

        $this->assertNull($office->fresh());
        $this->assertTrue($home->fresh()->is_default);
    }

    public function test_checkout_can_reuse_a_saved_address(): void
    {
        $this->primaryWarehouse();
        $user = $this->storeCustomer();
        $address = CustomerAddress::query()->create([
            'customer_id' => $user->customer->id,
            'label' => 'Home',
            'address_line' => '12 MG Road',
            'city' => 'Vijayawada',
            'pincode' => '520010',
            'is_default' => true,
        ]);

        $product = $this->onlineProduct();
        app(StoreCart::class)->add($product, 1);

        $this->actingAs($user)
            ->get(route('store.checkout'))
            ->assertOk()
            ->assertSee('Home')
            ->assertSee('12 MG Road');

        $this->actingAs($user)
            ->post(route('store.checkout.store'), [
                'address_source' => 'saved',
                'address_id' => $address->id,
            ])
            ->assertRedirect();

        $order = Order::query()->first();
        $this->assertNotNull($order);
        $this->assertSame('12 MG Road', $order->shipping_address);
        $this->assertSame('Vijayawada', $order->shipping_city);
        $this->assertSame('520010', $order->shipping_pincode);
        $this->assertSame(1, CustomerAddress::query()->count());
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
