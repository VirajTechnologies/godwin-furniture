<?php

namespace App\Support;

use App\Exceptions\InsufficientStock;
use App\Models\Agent;
use App\Models\Branch;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class StockLedger
{
    public function openWarehouseStock(ProductVariant $variant, Warehouse $warehouse, int $quantity, ?string $note, ?int $userId): Stock
    {
        return DB::transaction(function () use ($variant, $warehouse, $quantity, $note, $userId) {
            $stock = Stock::query()->create([
                'product_variant_id' => $variant->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => $quantity,
            ]);

            $stock->movements()->create([
                'type' => StockMovement::TYPE_OPENING,
                'quantity_change' => $quantity,
                'quantity_after' => $quantity,
                'note' => $note,
                'user_id' => $userId,
            ]);

            return $stock;
        });
    }

    public function adjust(Stock $stock, int $quantity, string $direction, ?string $note, ?int $userId): Stock
    {
        return DB::transaction(function () use ($stock, $quantity, $direction, $note, $userId) {
            $locked = Stock::query()->whereKey($stock->id)->lockForUpdate()->firstOrFail();
            $locked->loadMissing('variant.product');

            if ($direction === 'remove') {
                $this->decrease($locked, $quantity, StockMovement::TYPE_ADJUSTMENT, $note, $userId);
            } else {
                $this->increase($locked, $quantity, StockMovement::TYPE_ADJUSTMENT, $note, $userId);
            }

            return $locked->refresh();
        });
    }

    public function decrease(Stock $stock, int $quantity, string $type, ?string $note, ?int $userId): void
    {
        if ($quantity > $stock->quantity) {
            throw new InsufficientStock($stock->variant?->displayName() ?? 'This product', $stock->quantity, $quantity);
        }

        $after = $stock->quantity - $quantity;
        $stock->update(['quantity' => $after]);

        $stock->movements()->create([
            'type' => $type,
            'quantity_change' => -$quantity,
            'quantity_after' => $after,
            'note' => $note,
            'user_id' => $userId,
        ]);
    }

    public function increase(Stock $stock, int $quantity, string $type, ?string $note, ?int $userId): void
    {
        $after = $stock->quantity + $quantity;
        $stock->update(['quantity' => $after]);

        $stock->movements()->create([
            'type' => $type,
            'quantity_change' => $quantity,
            'quantity_after' => $after,
            'note' => $note,
            'user_id' => $userId,
        ]);
    }

    public function branchStock(ProductVariant $variant, Branch $branch): Stock
    {
        return $this->stockFor($variant, 'branch_id', $branch->id);
    }

    public function warehouseStock(ProductVariant $variant, Warehouse $warehouse): Stock
    {
        return $this->stockFor($variant, 'warehouse_id', $warehouse->id);
    }

    public function agentStock(ProductVariant $variant, Agent $agent): Stock
    {
        return $this->stockFor($variant, 'agent_id', $agent->id);
    }

    private function stockFor(ProductVariant $variant, string $column, int $id): Stock
    {
        $others = array_diff(['warehouse_id', 'branch_id', 'agent_id'], [$column]);

        $query = Stock::query()
            ->where('product_variant_id', $variant->id)
            ->where($column, $id);

        foreach ($others as $other) {
            $query->whereNull($other);
        }

        $stock = $query->lockForUpdate()->first();

        if ($stock) {
            return $stock;
        }

        return Stock::query()->create([
            'product_variant_id' => $variant->id,
            $column => $id,
            'quantity' => 0,
        ]);
    }
}
