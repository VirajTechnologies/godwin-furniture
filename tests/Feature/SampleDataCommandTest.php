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
        $this->assertDatabaseHas('categories', ['name' => 'Furniture', 'parent_id' => null]);
        $this->assertDatabaseHas('categories', ['name' => 'Living Room']);
        $this->assertDatabaseHas('categories', ['name' => 'Sofa']);
        $this->assertDatabaseHas('products', ['code' => 'SF001', 'is_online' => true, 'project_category' => 'Furniture']);
        $this->assertDatabaseHas('products', ['code' => 'OT001', 'status' => 'inactive']);
        $this->assertDatabaseHas('products', ['code' => 'GX0001']);
        $this->assertGreaterThanOrEqual(50, \App\Models\Category::query()->whereNotNull('parent_id')->count());
        $this->assertGreaterThanOrEqual(200, \App\Models\Product::query()->where('is_online', true)->count());
        $this->assertGreaterThanOrEqual(2, \App\Models\ProductVariant::query()
            ->where('product_id', \App\Models\Product::query()->where('code', 'SF001')->value('id'))
            ->count());

        $this->assertDatabaseHas('stock_transfers', ['code' => 'TR004', 'status' => 'dispatched']);
        $this->assertDatabaseHas('stock_transfers', ['code' => 'TR006', 'status' => 'cancelled']);
        $this->assertDatabaseHas('customers', ['phone' => '9848011001']);
        $this->assertDatabaseHas('orders', ['channel' => 'branch', 'status' => 'completed']);
        $this->assertDatabaseHas('agents', ['code' => 'AG001', 'name' => 'Sri Lakshmi Furniture Mart', 'commission_percent' => 5]);
        $this->assertDatabaseHas('users', ['email' => 'cashier.lakshmi@godwin.test']);
        $this->assertDatabaseHas('agents', ['code' => 'AG002', 'name' => 'Kalanjali Home Studio']);
        $this->assertDatabaseHas('agents', ['code' => 'AG003', 'name' => 'Deccan Living Gallery']);
        $this->assertDatabaseHas('orders', ['channel' => 'agent']);

        $lakshmi = \App\Models\Agent::query()->where('code', 'AG001')->firstOrFail();
        $sofa = \App\Models\Product::query()->where('code', 'SF001')->with('variants')->firstOrFail();
        $sofaVariant = $sofa->variants->firstOrFail();
        $this->assertDatabaseHas('order_items', [
            'product_id' => $sofa->id,
            'product_variant_id' => $sofaVariant->id,
            'unit_price' => 40000,
            'commission_amount' => 2000,
        ]);
        $this->assertDatabaseHas('agent_product_terms', [
            'agent_id' => $lakshmi->id,
            'product_variant_id' => $sofaVariant->id,
            'commission_percent' => null,
        ]);
        $bed = \App\Models\Product::query()->where('code', 'BD001')->with('variants')->firstOrFail();
        $this->assertDatabaseHas('agent_product_terms', [
            'agent_id' => $lakshmi->id,
            'product_variant_id' => $bed->variants->firstOrFail()->id,
            'commission_percent' => 4,
        ]);
        $this->assertDatabaseHas('stock_transfers', [
            'code' => 'TR007',
            'agent_id' => $lakshmi->id,
            'status' => 'received',
        ]);
        $this->assertDatabaseHas('stocks', [
            'product_variant_id' => $sofaVariant->id,
            'agent_id' => $lakshmi->id,
            'quantity' => 1,
        ]);
        $this->assertDatabaseHas('stock_requests', [
            'code' => 'R00005',
            'agent_id' => \App\Models\Agent::query()->where('code', 'AG003')->value('id'),
        ]);

        $this->artisan('sample:load')->assertFailed();
    }
}
