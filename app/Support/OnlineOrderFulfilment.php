<?php

namespace App\Support;

use App\Exceptions\InsufficientStock;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OnlineOrderFulfilment
{
    public function __construct(private StockLedger $ledger) {}

    public function confirm(Order $order): Order
    {
        $this->assertOnline($order);

        if ($order->status !== Order::STATUS_PLACED) {
            throw new RuntimeException('Only placed online orders can be confirmed.');
        }

        $order->update([
            'status' => Order::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ]);

        return $order->refresh();
    }

    public function dispatch(Order $order, int $userId): Order
    {
        $this->assertOnline($order);

        if ($order->status !== Order::STATUS_CONFIRMED) {
            throw new RuntimeException('Only confirmed online orders can be dispatched.');
        }

        if ($order->warehouse_id === null) {
            throw new RuntimeException('This online order has no warehouse assigned.');
        }

        return DB::transaction(function () use ($order, $userId) {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $locked->load(['items.product']);

            if ($locked->status !== Order::STATUS_CONFIRMED) {
                throw new RuntimeException('Only confirmed online orders can be dispatched.');
            }

            foreach ($locked->items as $item) {
                $stock = Stock::query()
                    ->where('product_id', $item->product_id)
                    ->where('warehouse_id', $locked->warehouse_id)
                    ->whereNull('branch_id')
                    ->lockForUpdate()
                    ->first();

                $available = $stock?->quantity ?? 0;

                if (! $stock || $available < $item->quantity) {
                    throw new InsufficientStock(
                        $item->product?->name ?? 'This product',
                        $available,
                        (int) $item->quantity,
                        'warehouse',
                    );
                }

                $this->ledger->decrease(
                    $stock,
                    (int) $item->quantity,
                    StockMovement::TYPE_SALE,
                    'Online order '.$locked->code,
                    $userId,
                );
            }

            $locked->update([
                'status' => Order::STATUS_DISPATCHED,
                'dispatched_at' => now(),
            ]);

            return $locked->refresh();
        });
    }

    public function complete(Order $order): Order
    {
        $this->assertOnline($order);

        if ($order->status !== Order::STATUS_DISPATCHED) {
            throw new RuntimeException('Only dispatched online orders can be completed.');
        }

        return DB::transaction(function () use ($order) {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $locked->load('payment');

            if ($locked->status !== Order::STATUS_DISPATCHED) {
                throw new RuntimeException('Only dispatched online orders can be completed.');
            }

            $locked->update([
                'status' => Order::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

            if ($locked->payment) {
                $locked->payment->update(['status' => Payment::STATUS_PAID]);
            }

            return $locked->refresh()->load('payment');
        });
    }

    private function assertOnline(Order $order): void
    {
        if ($order->channel !== Order::CHANNEL_ONLINE) {
            throw new RuntimeException('This action is only available for online orders.');
        }
    }
}
