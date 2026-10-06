<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class StoreCart
{
    public const SESSION_KEY = 'store_cart';

    public const MAX_QUANTITY = 20;

    /**
     * @return array<int, int> product_id => quantity
     */
    public function raw(): array
    {
        $items = Session::get(self::SESSION_KEY, []);

        if (! is_array($items)) {
            return [];
        }

        $clean = [];
        foreach ($items as $productId => $quantity) {
            $id = (int) $productId;
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

    public function add(Product $product, int $quantity = 1): void
    {
        $this->assertPurchasable($product);

        $quantity = max(1, min($quantity, self::MAX_QUANTITY));
        $items = $this->raw();
        $items[$product->id] = min(($items[$product->id] ?? 0) + $quantity, self::MAX_QUANTITY);
        $this->save($items);
    }

    public function update(Product $product, int $quantity): void
    {
        $this->assertPurchasable($product);

        $items = $this->raw();

        if ($quantity <= 0) {
            unset($items[$product->id]);
        } else {
            $items[$product->id] = min($quantity, self::MAX_QUANTITY);
        }

        $this->save($items);
    }

    public function remove(Product $product): void
    {
        $items = $this->raw();
        unset($items[$product->id]);
        $this->save($items);
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * @return Collection<int, object{
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

        $products = Product::query()
            ->with('images')
            ->whereIn('id', array_keys($raw))
            ->where('is_online', true)
            ->where('status', Product::STATUS_ACTIVE)
            ->get()
            ->keyBy('id');

        $lines = collect();
        $stale = false;

        foreach ($raw as $productId => $quantity) {
            $product = $products->get($productId);
            if (! $product) {
                $stale = true;
                continue;
            }

            $unitPrice = $product->storePrice();
            $mrp = $product->mrp();

            $lines->push((object) [
                'product' => $product,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => round($unitPrice * $quantity, 2),
                'mrp' => $mrp,
            ]);
        }

        if ($stale) {
            $this->save($lines->mapWithKeys(fn ($line) => [$line->product->id => $line->quantity])->all());
        }

        return $lines;
    }

    public function subtotal(): float
    {
        return round((float) $this->lines()->sum('line_total'), 2);
    }

    private function assertPurchasable(Product $product): void
    {
        if (! $product->is_online || ! $product->isActive()) {
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
