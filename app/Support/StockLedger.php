<?php

namespace App\Support;

use App\Exceptions\InsufficientStock;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class StockLedger
{
    public function openWarehouseStock(Product $product, Warehouse $warehouse, int $quantity, ?string $note, ?int $userId): Stock
    {
        return DB::transaction(function () use ($product, $warehouse, $quantity, $note, $userId) {
            $stock = Stock::query()->create([
                'product_id' => $product->id,
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

    public function setQuantity(Stock $stock, int $quantity, ?string $note, ?int $userId): Stock
    {
        return DB::transaction(function () use ($stock, $quantity, $note, $userId) {
            $change = $quantity - $stock->quantity;

            if ($change === 0) {
                return $stock;
            }

            $stock->update(['quantity' => $quantity]);

            $stock->movements()->create([
                'type' => StockMovement::TYPE_ADJUSTMENT,
                'quantity_change' => $change,
                'quantity_after' => $quantity,
                'note' => $note,
                'user_id' => $userId,
            ]);

            return $stock->refresh();
        });
    }

    public function decrease(Stock $stock, int $quantity, string $type, ?string $note, ?int $userId): void
    {
        if ($quantity > $stock->quantity) {
            throw new InsufficientStock($stock->product?->name ?? 'This product', $stock->quantity, $quantity);
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

    public function branchStock(Product $product, Branch $branch): Stock
    {
        $stock = Stock::query()
            ->where('product_id', $product->id)
            ->where('branch_id', $branch->id)
            ->whereNull('warehouse_id')
            ->lockForUpdate()
            ->first();

        if ($stock) {
            return $stock;
        }

        return Stock::query()->create([
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'quantity' => 0,
        ]);
    }
}
