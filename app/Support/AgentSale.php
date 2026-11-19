<?php

namespace App\Support;

use App\Exceptions\InsufficientStock;
use App\Models\Agent;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AgentSale
{
    public function __construct(private StockLedger $ledger) {}

    /**
     * @param  list<array{product_variant_id: int, quantity: int, unit_price?: float|int|string}>  $lines
     * @param  array{
     *     delivery_type?: string,
     *     shipping_address?: string|null,
     *     shipping_city?: string|null,
     *     shipping_pincode?: string|null,
     *     notes?: string|null
     * }  $fulfilment
     */
    public function complete(Agent $agent, Customer $customer, array $lines, string $method, int $userId, array $fulfilment = []): Order
    {
        return DB::transaction(function () use ($agent, $customer, $lines, $method, $userId, $fulfilment) {
            $isDelivery = ($fulfilment['delivery_type'] ?? Order::DELIVERY_PICKUP) === Order::DELIVERY_DELIVERY;

            $order = Order::query()->create([
                'code' => 'PENDING',
                'channel' => Order::CHANNEL_AGENT,
                'customer_id' => $customer->id,
                'agent_id' => $agent->id,
                'placed_by' => $userId,
                'total' => 0,
                'status' => Order::STATUS_COMPLETED,
                'delivery_type' => $isDelivery ? Order::DELIVERY_DELIVERY : Order::DELIVERY_PICKUP,
                'notes' => $fulfilment['notes'] ?? null,
                'shipping_address' => $isDelivery ? ($fulfilment['shipping_address'] ?? null) : null,
                'shipping_city' => $isDelivery ? ($fulfilment['shipping_city'] ?? null) : null,
                'shipping_pincode' => $isDelivery ? ($fulfilment['shipping_pincode'] ?? null) : null,
                'completed_at' => now(),
            ]);

            $code = 'A'.str_pad((string) $order->id, 5, '0', STR_PAD_LEFT);
            $order->update(['code' => $code]);
            $total = 0;

            foreach ($lines as $line) {
                $variant = ProductVariant::query()->with('product')->findOrFail($line['product_variant_id']);

                if (! $variant->isActive() || ! $variant->product?->isActive()) {
                    throw new RuntimeException($variant->displayName().' is not available for this shop.');
                }

                $floor = round($variant->priceForAgent($agent), 2);
                $unitPrice = isset($line['unit_price']) ? round((float) $line['unit_price'], 2) : $floor;
                $commissionPercent = $variant->commissionPercentForAgent($agent);

                if ($unitPrice < $floor) {
                    throw new RuntimeException($variant->displayName().' cannot be sold below ₹'.number_format($floor, 2).'.');
                }

                $stock = Stock::query()
                    ->where('product_variant_id', $variant->id)
                    ->where('agent_id', $agent->id)
                    ->whereNull('warehouse_id')
                    ->whereNull('branch_id')
                    ->lockForUpdate()
                    ->first();

                $available = $stock?->quantity ?? 0;

                if (! $stock || $available < $line['quantity']) {
                    throw new InsufficientStock($variant->displayName(), $available, $line['quantity'], 'shop');
                }

                $lineTotal = round($unitPrice * $line['quantity'], 2);
                $commission = round($lineTotal * $commissionPercent / 100, 2);
                $total += $lineTotal;

                $order->items()->create([
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'commission_percent' => $commissionPercent,
                    'commission_amount' => $commission,
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
