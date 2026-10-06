<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_and_sign_in(): void
    {
        $this->post(route('store.register.store'), [
            'name' => 'Priya Nair',
            'phone' => '9848011222',
            'email' => 'priya@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ])->assertRedirect(route('store.home'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'priya@example.com',
            'phone' => '9848011222',
            'role_id' => null,
        ]);
        $this->assertDatabaseHas('customers', [
            'phone' => '9848011222',
            'email' => 'priya@example.com',
            'user_id' => auth()->id(),
        ]);

        $this->post(route('store.logout'))->assertRedirect(route('store.home'));
        $this->assertGuest();

        $this->post(route('store.login.store'), [
            'email' => 'priya@example.com',
            'password' => 'Password1!',
        ])->assertRedirect(route('store.home'));

        $this->assertAuthenticated();
    }

    public function test_register_rejects_phone_already_linked_to_an_account(): void
    {
        $user = User::query()->create([
            'name' => 'Existing',
            'email' => 'existing@example.com',
            'phone' => '9848011333',
            'password' => 'Password1!',
            'status' => 'active',
        ]);
        Customer::query()->create([
            'user_id' => $user->id,
            'name' => 'Existing',
            'phone' => '9848011333',
            'email' => 'existing@example.com',
            'status' => Customer::STATUS_ACTIVE,
        ]);

        $this->from(route('store.register'))
            ->post(route('store.register.store'), [
                'name' => 'Someone Else',
                'phone' => '9848011333',
                'email' => 'other@example.com',
                'password' => 'Password1!',
                'password_confirmation' => 'Password1!',
            ])
            ->assertRedirect(route('store.register'))
            ->assertSessionHasErrors('phone');
    }
}
