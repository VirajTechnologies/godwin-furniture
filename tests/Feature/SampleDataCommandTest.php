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
        $this->assertDatabaseMissing('products', ['code' => 'IX0001']);
        $this->assertSame(0, \App\Models\Product::query()->where('project_category', 'Interiors')->count());
        $this->assertDatabaseMissing('categories', ['name' => 'Interiors', 'parent_id' => null]);
        $this->assertGreaterThanOrEqual(50, \App\Models\Category::query()->whereNotNull('parent_id')->count());
        $this->assertGreaterThanOrEqual(200, \App\Models\Product::query()->where('is_online', true)->count());
        $this->assertGreaterThanOrEqual(2, \App\Models\ProductVariant::query()
            ->where('product_id', \App\Models\Product::query()->where('code', 'SF001')->value('id'))
            ->count());

        $this->assertDatabaseHas('stock_transfers', ['code' => 'TR004', 'status' => 'dispatched']);
        $this->assertDatabaseHas('stock_transfers', ['code' => 'TR006', 'status' => 'cancelled']);
        $this->assertDatabaseHas('customers', ['phone' => '9848011001', 'email' => 'customer@godwin.test']);
        $this->assertDatabaseHas('users', ['email' => 'customer@godwin.test', 'role_id' => null]);
        $this->assertDatabaseHas('customer_addresses', ['city' => 'Vijayawada', 'pincode' => '520010']);
        $this->assertDatabaseHas('orders', ['channel' => 'branch', 'status' => 'completed']);
        $this->assertDatabaseHas('orders', ['channel' => 'online', 'status' => 'completed']);
        $this->assertDatabaseHas('orders', ['channel' => 'online', 'status' => 'placed']);
        $this->assertDatabaseHas('agents', ['code' => 'AG001', 'name' => 'Sri Lakshmi Furniture Mart', 'commission_percent' => 5]);
        $this->assertDatabaseHas('users', ['email' => 'cashier.lakshmi@godwin.test']);
        $this->assertDatabaseHas('agents', ['code' => 'AG002', 'name' => 'Kalanjali Home Studio']);
        $this->assertDatabaseHas('agents', ['code' => 'AG003', 'name' => 'Deccan Living Gallery']);
        $this->assertDatabaseHas('orders', ['channel' => 'agent']);

        $sofa = \App\Models\Product::query()->where('code', 'SF001')->with('variants.images')->firstOrFail();
        $this->assertGreaterThanOrEqual(2, $sofa->variants->count());
        foreach ($sofa->variants as $variant) {
            $this->assertDatabaseHas('stocks', [
                'product_variant_id' => $variant->id,
                'warehouse_id' => \App\Models\Warehouse::query()->where('code', 'WH001')->value('id'),
                'branch_id' => null,
                'agent_id' => null,
            ]);
            $this->assertGreaterThan(
                0,
                (int) \App\Models\Stock::query()
                    ->where('product_variant_id', $variant->id)
                    ->where('warehouse_id', \App\Models\Warehouse::query()->where('code', 'WH001')->value('id'))
                    ->whereNull('branch_id')
                    ->whereNull('agent_id')
                    ->value('quantity'),
            );
        }
        $sofaUrl = $sofa->variants->first()->images->sortBy('sort_order')->first()?->url;
        $this->assertNotNull($sofaUrl);
        $this->assertStringContainsString('photo-1555041469', (string) $sofaUrl);

        $wardrobe = \App\Models\Product::query()->where('code', 'ST001')->with('variants.images')->firstOrFail();
        $wardrobePrimaryUrls = $wardrobe->variants->map(
            fn ($variant) => $variant->images->sortBy('sort_order')->first()?->url
        )->filter()->unique()->values();
        $this->assertSame(
            $wardrobe->variants->count(),
            $wardrobePrimaryUrls->count(),
            'Each wardrobe colourway should have a distinct primary image for the store PDP.',
        );
        foreach ($wardrobePrimaryUrls as $url) {
            $this->assertTrue(
                str_contains($url, '813688')
                    || str_contains($url, '6585756')
                    || str_contains($url, '1454806')
                    || str_contains($url, 'photo-1595428774223'),
                'Wardrobe sample images should come from the wardrobe photo pool, got: '.$url,
            );
        }

        $lakshmi = \App\Models\Agent::query()->where('code', 'AG001')->firstOrFail();
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
