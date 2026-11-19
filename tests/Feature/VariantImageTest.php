<?php

namespace Tests\Feature;

use App\Models\ProductImage;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCatalog;
use Tests\TestCase;

class VariantImageTest extends TestCase
{
    use CreatesCatalog;
    use RefreshDatabase;

    public function test_admin_can_save_shared_and_variant_images(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'project_category' => 'Furniture',
            'room' => 'Living Room',
            'product_category' => 'Sofa',
            'design_type' => 'Chester',
            'code' => 'SF100',
            'name' => 'Image Sofa',
            'unit' => 'Piece',
            'base_price' => '42000',
            'is_online' => '1',
            'is_featured' => '0',
            'status' => 'active',
            'image_urls' => "https://example.com/shared.jpg",
            'variants' => [
                [
                    'colour' => 'Walnut',
                    'finish' => 'Matte',
                    'upholstery' => 'Velvet',
                    'price' => '42000',
                    'compare_at_price' => '54999',
                    'status' => 'active',
                    'image_urls' => "https://example.com/walnut-1.jpg\nhttps://example.com/walnut-2.jpg",
                ],
                [
                    'colour' => 'Charcoal',
                    'finish' => 'Matte',
                    'upholstery' => 'Linen blend',
                    'price' => '43000',
                    'status' => 'active',
                    'image_urls' => 'https://example.com/charcoal.jpg',
                ],
            ],
        ])->assertRedirect(route('admin.products.index'));

        $product = \App\Models\Product::query()->where('code', 'SF100')->with(['images', 'variants.images'])->firstOrFail();

        $this->assertDatabaseHas('product_images', [
            'product_id' => $product->id,
            'product_variant_id' => null,
            'url' => 'https://example.com/shared.jpg',
        ]);
        $this->assertSame(2, $product->variants->firstWhere('colour', 'Walnut')->images->count());
        $this->assertSame(1, $product->variants->firstWhere('colour', 'Charcoal')->images->count());
        $this->assertSame(
            ['https://example.com/walnut-1.jpg', 'https://example.com/walnut-2.jpg'],
            $product->galleryFor($product->variants->firstWhere('colour', 'Walnut'))->pluck('url')->all(),
        );
    }

    public function test_store_pdp_exposes_variant_images_in_picker_payload(): void
    {
        Warehouse::query()->create([
            'code' => 'WH001',
            'name' => 'Main Warehouse',
            'address_line' => 'Industrial Area',
            'pincode' => '520001',
            'is_primary' => true,
            'status' => 'active',
        ]);

        $product = $this->catalogProduct([
            'code' => 'SF101',
            'slug' => 'image-sofa-pdp',
            'name' => 'Image Sofa PDP',
            'base_price' => 38999,
            'is_online' => true,
        ], [
            [
                'colour' => 'Walnut',
                'finish' => 'Matte',
                'upholstery' => 'Velvet',
                'price' => 38999,
                'compare_at_price' => 54999,
            ],
            [
                'colour' => 'Charcoal',
                'finish' => 'Matte',
                'upholstery' => 'Velvet',
                'price' => 39999,
                'compare_at_price' => 55999,
            ],
        ]);

        $walnut = $product->variants->firstWhere('colour', 'Walnut');
        $charcoal = $product->variants->firstWhere('colour', 'Charcoal');

        ProductImage::query()->create([
            'product_id' => $product->id,
            'product_variant_id' => null,
            'url' => 'https://example.com/shared.jpg',
            'sort_order' => 0,
        ]);
        ProductImage::query()->create([
            'product_id' => $product->id,
            'product_variant_id' => $walnut->id,
            'url' => 'https://example.com/walnut.jpg',
            'sort_order' => 0,
        ]);

        $html = $this->get(route('store.product', $product->slug))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('https://example.com/walnut.jpg', $html);
        $this->assertStringContainsString('variant-data', $html);
        // Charcoal has no own photos, so the picker payload falls back to the shared gallery.
        $this->assertMatchesRegularExpression(
            '/"colour"\s*:\s*"Charcoal".*?"images"\s*:\s*\[\s*"https:\\\\\/\\\\\/example\.com\\\\\/shared\.jpg"/s',
            $html,
        );
        $this->assertNotNull($charcoal);
    }
}
