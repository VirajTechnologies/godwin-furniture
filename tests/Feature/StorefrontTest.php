<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_static_store_pages_are_public(): void
    {
        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('Godwin')
            ->assertDontSee('Showroom POS');

        $this->get(route('store.catalog'))
            ->assertOk()
            ->assertSee('All Products');

        $this->get(route('store.furniture-on-rent'))
            ->assertOk()
            ->assertSee('Coming Soon')
            ->assertSee('Furniture on Rent');

        $this->get(route('store.cart'))
            ->assertOk()
            ->assertSee('Your cart is empty');

        $this->get(route('store.login'))
            ->assertOk()
            ->assertSee('Welcome Back');

        $this->get(route('store.register'))
            ->assertOk()
            ->assertSee('Create Account');
    }
}
