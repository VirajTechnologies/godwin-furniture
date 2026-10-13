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
        $transfer->loadMissing('items');

        $this->complete(
            $transfer,
            $transfer->items->map(fn ($item) => [
                'id' => $item->id,
                'received_quantity' => $item->quantity,
                'returned_quantity' => 0,
            ])->all(),
            $userId,
        );
    }

    /**
     * @param  list<array{id: int, received_quantity: int, returned_quantity: int}>  $lines
     */
    public function complete(StockTransfer $transfer, array $lines, int $userId, ?string $reason = null): void
    {
        DB::transaction(function () use ($transfer, $lines, $userId, $reason) {
            $locked = StockTransfer::query()->whereKey($transfer->id)->lockForUpdate()->firstOrFail();

            if (! $locked->isDispatched()) {
                throw new RuntimeException('Only a dispatched transfer can be completed.');
            }

            $locked->load(['items.product', 'branch', 'warehouse']);
            $posted = collect($lines)->keyBy(fn (array $line) => (int) $line['id']);

            if ($posted->count() !== $locked->items->count()) {
                throw new RuntimeException('Enter an accepted quantity for every line.');
            }

            $receivedTotal = 0;
            $returnedTotal = 0;
            $splits = [];

            foreach ($locked->items as $item) {
                $line = $posted->get($item->id);

                if (! is_array($line)) {
                    throw new RuntimeException('Enter an accepted quantity for every line.');
                }

                $received = (int) $line['received_quantity'];
                $returned = (int) $line['returned_quantity'];

                if ($received < 0 || $returned < 0 || $received + $returned !== $item->quantity) {
                    throw new RuntimeException('Accepted and returned quantities must add up to the dispatched quantity.');
                }

                $splits[] = [$item, $received, $returned];
                $receivedTotal += $received;
                $returnedTotal += $returned;
            }

            if ($returnedTotal > 0 && ! filled($reason)) {
                throw new RuntimeException('A return reason is required when any quantity is returned.');
            }

            $acceptNote = 'Transfer '.$locked->code;
            $returnNote = 'Return of transfer '.$locked->code;

            foreach ($splits as [$item, $received, $returned]) {
                if ($received > 0) {
                    $branchStock = $this->ledger->branchStock($item->product, $locked->branch);
                    $this->ledger->increase($branchStock, $received, StockMovement::TYPE_TRANSFER_IN, $acceptNote, $userId);
                }

                if ($returned > 0) {
                    $warehouseStock = $this->ledger->warehouseStock($item->product, $locked->warehouse);
                    $this->ledger->increase($warehouseStock, $returned, StockMovement::TYPE_TRANSFER_RETURN, $returnNote, $userId);
                }

                $item->update([
                    'received_quantity' => $received,
                    'returned_quantity' => $returned,
                ]);
            }

            $status = match (true) {
                $returnedTotal === 0 => StockTransfer::STATUS_RECEIVED,
                $receivedTotal === 0 => StockTransfer::STATUS_RETURNED,
                default => StockTransfer::STATUS_PARTIAL,
            };

            $locked->update([
                'status' => $status,
                'received_at' => $receivedTotal > 0 ? now() : null,
                'returned_at' => $returnedTotal > 0 ? now() : null,
                'return_reason' => $returnedTotal > 0 ? $reason : null,
            ]);

            if ($status === StockTransfer::STATUS_RETURNED) {
                $locked->stockRequest()?->update([
                    'stock_transfer_id' => null,
                ]);
            }
        });
    }
}
