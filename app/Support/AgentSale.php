<?php

namespace App\Support;

use App\Exceptions\InsufficientStock;
use App\Models\Agent;
use App\Models\AgentProductTerm;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AgentSale
{
    public function __construct(private StockLedger $ledger) {}

    /**
     * @param  list<array{product_id: int, quantity: int, unit_price: float|int|string}>  $lines
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
                $product = Product::query()->findOrFail($line['product_id']);
                $term = AgentProductTerm::query()
                    ->where('agent_id', $agent->id)
                    ->where('product_id', $product->id)
                    ->first();

                if ($term === null) {
                    throw new RuntimeException($product->name.' is not available for this shop.');
                }

                $mrp = $product->mrp();

                if ($mrp === null) {
                    throw new RuntimeException($product->name.' has no MRP.');
                }

                $unitPrice = round((float) $line['unit_price'], 2);
                $minimum = round((float) $term->minimum_price, 2);

                if ($unitPrice < $minimum || $unitPrice > $mrp) {
                    throw new RuntimeException($product->name.' must be sold between ₹'.number_format($minimum, 2).' and ₹'.number_format($mrp, 2).'.');
                }

                $stock = Stock::query()
                    ->where('product_id', $product->id)
                    ->where('agent_id', $agent->id)
                    ->whereNull('warehouse_id')
                    ->whereNull('branch_id')
                    ->lockForUpdate()
                    ->first();

                $available = $stock?->quantity ?? 0;

                if (! $stock || $available < $line['quantity']) {
                    throw new InsufficientStock($product->name, $available, $line['quantity'], 'shop');
                }

                $lineTotal = round($unitPrice * $line['quantity'], 2);
                $commission = round($lineTotal * ((float) $term->commission_percent) / 100, 2);
                $total += $lineTotal;

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'commission_percent' => $term->commission_percent,
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
