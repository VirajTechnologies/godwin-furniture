<?php

namespace App\Support;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class StoreCart
{
    /**
     * Bag items are keyed by variant, so the key differs from the old product bag.
     */
    public const SESSION_KEY = 'store_cart_variants';

    public const MAX_QUANTITY = 20;

    /**
     * @return array<int, int> product_variant_id => quantity
     */
    public function raw(): array
    {
        $items = Session::get(self::SESSION_KEY, []);

        if (! is_array($items)) {
            return [];
        }

        $clean = [];
        foreach ($items as $variantId => $quantity) {
            $id = (int) $variantId;
            $qty = (int) $quantity;
            if ($id > 0 && $qty > 0) {
                $clean[$id] = min($qty, self::MAX_QUANTITY);
            }
        }

        return $clean;
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    public function add(ProductVariant $variant, int $quantity = 1): void
    {
        $this->assertPurchasable($variant);

        $quantity = max(1, min($quantity, self::MAX_QUANTITY));
        $items = $this->raw();
        $items[$variant->id] = min(($items[$variant->id] ?? 0) + $quantity, self::MAX_QUANTITY);
        $this->save($items);
    }

    public function update(ProductVariant $variant, int $quantity): void
    {
        $this->assertPurchasable($variant);

        $items = $this->raw();

        if ($quantity <= 0) {
            unset($items[$variant->id]);
        } else {
            $items[$variant->id] = min($quantity, self::MAX_QUANTITY);
        }

        $this->save($items);
    }

    public function remove(ProductVariant $variant): void
    {
        $items = $this->raw();
        unset($items[$variant->id]);
        $this->save($items);
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * @return Collection<int, object{
     *     variant: ProductVariant,
     *     product: Product,
     *     quantity: int,
     *     unit_price: float,
     *     line_total: float,
     *     mrp: float|null
     * }>
     */
    public function lines(): Collection
    {
        $raw = $this->raw();
        if ($raw === []) {
            return collect();
        }

        $variants = ProductVariant::query()
            ->with('product.images')
            ->whereIn('id', array_keys($raw))
            ->where('status', ProductVariant::STATUS_ACTIVE)
            ->whereHas('product', function ($query): void {
                $query->where('is_online', true)->where('status', Product::STATUS_ACTIVE);
            })
            ->get()
            ->keyBy('id');

        $lines = collect();
        $stale = false;

        foreach ($raw as $variantId => $quantity) {
            $variant = $variants->get($variantId);
            if (! $variant) {
                $stale = true;
                continue;
            }

            $unitPrice = $variant->storePrice();

            $lines->push((object) [
                'variant' => $variant,
                'product' => $variant->product,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => round($unitPrice * $quantity, 2),
                'mrp' => $variant->mrp(),
            ]);
        }

        if ($stale) {
            $this->save($lines->mapWithKeys(fn ($line) => [$line->variant->id => $line->quantity])->all());
        }

        return $lines;
    }

    public function subtotal(): float
    {
        return round((float) $this->lines()->sum('line_total'), 2);
    }

    private function assertPurchasable(ProductVariant $variant): void
    {
        $variant->loadMissing('product');

        if (! $variant->isActive() || ! $variant->product?->is_online || ! $variant->product->isActive()) {
            abort(422, 'This product is not available online.');
        }
    }

    /**
     * @param  array<int, int>  $items
     */
    private function save(array $items): void
    {
        Session::put(self::SESSION_KEY, $items);
    }
}
