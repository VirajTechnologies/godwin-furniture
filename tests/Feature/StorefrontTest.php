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
            ->assertSee('Godwin Groups')
            ->assertDontSee('Showroom POS');

        $this->get(route('store.catalog'))
            ->assertOk()
            ->assertSee('Furniture Catalog');

        $this->get(route('store.product'))
            ->assertOk()
            ->assertSee('Godwin Imperial');

        $this->get(route('store.cart'))
            ->assertOk()
            ->assertSee('Your Shopping Bag');

        $this->get(route('store.login'))
            ->assertOk()
            ->assertSee('Welcome Back');

        $this->get(route('store.register'))
            ->assertOk()
            ->assertSee('Create Priority Account');
    }
}
