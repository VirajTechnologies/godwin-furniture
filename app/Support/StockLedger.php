<?php

namespace App\Support;

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
}
