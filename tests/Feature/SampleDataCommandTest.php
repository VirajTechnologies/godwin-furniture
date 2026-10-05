<?php

namespace Tests\Feature;

use Database\Seeders\AdminUserSeeder;
use Database\Seeders\CitySeeder;
use Database\Seeders\DistrictSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\StateSeeder;
use Database\Seeders\WarehouseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SampleDataCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_sample_load_fills_showrooms_stock_and_sales(): void
    {
        $this->seed([
            RoleSeeder::class,
            AdminUserSeeder::class,
            StateSeeder::class,
            DistrictSeeder::class,
            CitySeeder::class,
            WarehouseSeeder::class,
        ]);

        $this->artisan('sample:load')->assertSuccessful();

        $this->assertDatabaseHas('branches', ['code' => 'BR001', 'name' => 'Vijayawada Showroom']);
        $this->assertDatabaseHas('categories', ['slug' => 'living-room', 'parent_id' => null]);
        $this->assertDatabaseHas('categories', ['slug' => '2-seater-sofas']);
        $this->assertDatabaseHas('categories', ['slug' => '8-seater-grand-dining-sets']);
        $this->assertDatabaseHas('categories', ['slug' => 'handwoven-wool-carpets']);
        $this->assertDatabaseHas('products', ['code' => 'SF001', 'is_online' => true]);
        $this->assertDatabaseHas('products', ['code' => 'SF004', 'is_online' => true]);
        $this->assertDatabaseHas('products', ['code' => 'FN001', 'is_online' => true]);
        $this->assertDatabaseHas('products', ['code' => 'OT001', 'status' => 'inactive']);
        $this->assertGreaterThanOrEqual(40, \App\Models\Category::query()->whereNotNull('parent_id')->count());
        $this->assertGreaterThanOrEqual(20, \App\Models\Product::query()->where('is_online', true)->count());
        $this->assertDatabaseHas('stock_transfers', ['code' => 'TR004', 'status' => 'dispatched']);
        $this->assertDatabaseHas('stock_transfers', ['code' => 'TR006', 'status' => 'cancelled']);
        $this->assertDatabaseHas('customers', ['phone' => '9848011001']);
        $this->assertDatabaseHas('orders', ['channel' => 'branch', 'status' => 'completed']);

        $this->artisan('sample:load')->assertFailed();
    }
}
