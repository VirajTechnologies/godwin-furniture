<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Support\StoreCart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCatalog;
use Tests\TestCase;

class StorefrontCartTest extends TestCase
{
    use CreatesCatalog;
    use RefreshDatabase;

    public function test_guest_can_add_an_online_product_to_the_bag(): void
    {
        $product = $this->onlineProduct();

        $this->from(route('store.product', $product->slug))
            ->post(route('store.cart.add'), [
                'product_id' => $product->id,
                'quantity' => 1,
            ])
            ->assertRedirect(route('store.product', $product->slug));

        $this->get(route('store.cart'))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('1 Item')
            ->assertSee(number_format($product->storePrice(), 0));
    }

    public function test_buy_now_adds_the_product_and_opens_the_bag(): void
    {
        $product = $this->onlineProduct();

        $this->post(route('store.cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'redirect' => 'cart',
        ])->assertRedirect(route('store.cart'));

        $this->assertSame(1, app(StoreCart::class)->count());
    }

    public function test_guest_can_update_and_remove_bag_items(): void
    {
        $product = $this->onlineProduct();
        app(StoreCart::class)->add($product, 1);

        $this->patch(route('store.cart.update', $product), ['quantity' => 3])
            ->assertRedirect(route('store.cart'));

        $this->assertSame(3, app(StoreCart::class)->count());

        $this->delete(route('store.cart.remove', $product))
            ->assertRedirect(route('store.cart'));

        $this->assertTrue(app(StoreCart::class)->isEmpty());
    }

    public function test_offline_products_cannot_be_added_to_the_bag(): void
    {
        $category = $this->subcategory();
        $product = Product::query()->create([
            'category_id' => $category->id,
            'code' => 'OFF01',
            'slug' => 'offline-sofa',
            'name' => 'Showroom Only Sofa',
            'unit' => 'Piece',
            'selling_price' => 42000,
            'is_online' => false,
            'status' => 'active',
        ]);

        $this->post(route('store.cart.add'), [
            'product_id' => $product->id,
        ])->assertStatus(422);

        $this->assertTrue(app(StoreCart::class)->isEmpty());
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
}
