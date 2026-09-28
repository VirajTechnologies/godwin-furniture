<?php

namespace App\Support;

use App\Exceptions\InsufficientStock;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockTransferWorkflow
{
    public function __construct(private StockLedger $ledger) {}

    public function dispatch(StockTransfer $transfer, int $userId): void
    {
        DB::transaction(function () use ($transfer, $userId) {
            $locked = StockTransfer::query()->whereKey($transfer->id)->lockForUpdate()->firstOrFail();

            if (! $locked->isDraft()) {
                throw new RuntimeException('Only a draft transfer can be dispatched.');
            }

            $locked->load('items.product');
            $note = 'Transfer '.$locked->code;

            foreach ($locked->items as $item) {
                $stock = Stock::query()
                    ->where('product_id', $item->product_id)
                    ->where('warehouse_id', $locked->warehouse_id)
                    ->whereNull('branch_id')
                    ->lockForUpdate()
                    ->first();

                $available = $stock?->quantity ?? 0;

                if (! $stock || $available < $item->quantity) {
                    throw new InsufficientStock($item->product?->name ?? 'This product', $available, $item->quantity);
                }

                $this->ledger->decrease($stock, $item->quantity, StockMovement::TYPE_TRANSFER_OUT, $note, $userId);
            }

            $locked->update([
                'status' => StockTransfer::STATUS_DISPATCHED,
                'dispatched_at' => now(),
            ]);
        });
    }

    public function receive(StockTransfer $transfer, int $userId): void
    {
        DB::transaction(function () use ($transfer, $userId) {
            $locked = StockTransfer::query()->whereKey($transfer->id)->lockForUpdate()->firstOrFail();

            if (! $locked->isDispatched()) {
                throw new RuntimeException('Only a dispatched transfer can be received.');
            }

            $locked->load(['items.product', 'branch']);
            $note = 'Transfer '.$locked->code;

            foreach ($locked->items as $item) {
                $stock = $this->ledger->branchStock($item->product, $locked->branch);
                $this->ledger->increase($stock, $item->quantity, StockMovement::TYPE_TRANSFER_IN, $note, $userId);
            }

            $locked->update([
                'status' => StockTransfer::STATUS_RECEIVED,
                'received_at' => now(),
            ]);
        });
    }
}
