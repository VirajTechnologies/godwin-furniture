<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StorefrontAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_account_page(): void
    {
        $this->get(route('store.account'))
            ->assertRedirect(route('store.login'));
    }

    public function test_customer_can_view_and_update_account_details(): void
    {
        $user = $this->storeCustomer();

        $this->actingAs($user)
            ->get(route('store.account'))
            ->assertOk()
            ->assertSee('My Account')
            ->assertSee($user->email)
            ->assertSee($user->phone);

        $this->actingAs($user)
            ->put(route('store.account.update'), [
                'name' => 'Updated Name',
                'phone' => '9848099999',
                'email' => 'updated@example.com',
            ])
            ->assertRedirect(route('store.account'));

        $user->refresh();
        $this->assertSame('Updated Name', $user->name);
        $this->assertSame('9848099999', $user->phone);
        $this->assertSame('updated@example.com', $user->email);
        $this->assertSame('Updated Name', $user->customer->name);
        $this->assertSame('9848099999', $user->customer->phone);
        $this->assertSame('updated@example.com', $user->customer->email);
    }

    public function test_customer_cannot_take_another_customers_phone(): void
    {
        $user = $this->storeCustomer([
            'phone' => '9848011001',
            'email' => 'one@example.com',
        ]);

        Customer::query()->create([
            'name' => 'Someone Else',
            'phone' => '9848011222',
            'status' => Customer::STATUS_ACTIVE,
        ]);

        $this->actingAs($user)
            ->from(route('store.account'))
            ->put(route('store.account.update'), [
                'name' => $user->name,
                'phone' => '9848011222',
                'email' => $user->email,
            ])
            ->assertRedirect(route('store.account'))
            ->assertSessionHasErrors('phone');
    }

    public function test_customer_can_change_password(): void
    {
        $user = $this->storeCustomer();

        $this->actingAs($user)
            ->put(route('store.account.password'), [
                'current_password' => 'Password1!',
                'password' => 'NewPassword1!',
                'password_confirmation' => 'NewPassword1!',
            ])
            ->assertRedirect(route('store.account'));

        $this->assertTrue(Hash::check('NewPassword1!', $user->fresh()->password));

        $this->post(route('store.logout'));

        $this->post(route('store.login.store'), [
            'email' => $user->email,
            'password' => 'NewPassword1!',
        ])->assertRedirect(route('store.home'));

        $this->assertAuthenticated();
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
}
