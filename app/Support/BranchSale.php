<?php

namespace App\Support;

use App\Exceptions\InsufficientStock;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BranchSale
{
    public function __construct(private StockLedger $ledger) {}

    /**
     * @param  list<array{product_id: int, quantity: int}>  $lines
     */
    public function complete(Branch $branch, Customer $customer, array $lines, string $method, int $userId, ?string $notes): Order
    {
        return DB::transaction(function () use ($branch, $customer, $lines, $method, $userId, $notes) {
            $order = Order::query()->create([
                'code' => 'T'.substr((string) Str::ulid(), 0, 19),
                'channel' => Order::CHANNEL_BRANCH,
                'customer_id' => $customer->id,
                'branch_id' => $branch->id,
                'placed_by' => $userId,
                'total' => 0,
                'status' => Order::STATUS_COMPLETED,
                'notes' => $notes,
            ]);

            $code = 'S'.str_pad((string) $order->id, 5, '0', STR_PAD_LEFT);
            $order->update(['code' => $code]);
            $total = 0;

            foreach ($lines as $line) {
                $product = Product::query()->findOrFail($line['product_id']);
                $stock = Stock::query()
                    ->where('product_id', $product->id)
                    ->where('branch_id', $branch->id)
                    ->whereNull('warehouse_id')
                    ->lockForUpdate()
                    ->first();

                $available = $stock?->quantity ?? 0;

                if (! $stock || $available < $line['quantity']) {
                    throw new InsufficientStock($product->name, $available, $line['quantity'], 'branch');
                }

                $unitPrice = $product->priceForBranch($branch);
                $lineTotal = round($unitPrice * $line['quantity'], 2);
                $total += $lineTotal;

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);

                $this->ledger->decrease($stock, $line['quantity'], StockMovement::TYPE_SALE, 'Sale '.$code, $userId);
            }

            $order->update(['total' => round($total, 2)]);
            $order->payment()->create([
                'method' => $method,
                'amount' => round($total, 2),
                'status' => Payment::STATUS_PAID,
            ]);

            return $order->refresh();
        });
    }
}
