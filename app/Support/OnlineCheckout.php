<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class OnlineCheckout
{
    public function __construct(
        private StoreCart $cart,
        private StoreCustomerAccount $accounts,
    ) {}

    /**
     * @param  array{
     *     shipping_address: string,
     *     shipping_city: string,
     *     shipping_pincode: string,
     *     notes?: string|null,
     *     save_address?: bool,
     *     address_label?: string|null
     * }  $shipping
     */
    public function place(User $user, array $shipping): Order
    {
        $lines = $this->cart->lines();

        if ($lines->isEmpty()) {
            throw new RuntimeException('Your bag is empty.');
        }

        $warehouse = Warehouse::primaryForOnline();

        if ($warehouse === null) {
            throw new RuntimeException('Online orders are unavailable right now. Please try again later.');
        }

        return DB::transaction(function () use ($user, $shipping, $lines, $warehouse) {
            $customer = $this->accounts->ensureFor($user);

            if ($shipping['save_address'] ?? false) {
                $this->accounts->saveAddress($customer, [
                    'label' => $shipping['address_label'] ?? null,
                    'address_line' => $shipping['shipping_address'],
                    'city' => $shipping['shipping_city'],
                    'pincode' => $shipping['shipping_pincode'],
                    'is_default' => $customer->addresses()->count() === 0,
                ]);
            }

            $order = Order::query()->create([
                'code' => 'T'.substr((string) Str::ulid(), 0, 19),
                'channel' => Order::CHANNEL_ONLINE,
                'customer_id' => $customer->id,
                'warehouse_id' => $warehouse->id,
                'placed_by' => null,
                'total' => 0,
                'status' => Order::STATUS_PLACED,
                'delivery_type' => Order::DELIVERY_DELIVERY,
                'notes' => $shipping['notes'] ?? null,
                'shipping_address' => $shipping['shipping_address'],
                'shipping_city' => $shipping['shipping_city'],
                'shipping_pincode' => $shipping['shipping_pincode'],
            ]);

            $code = 'W'.str_pad((string) $order->id, 5, '0', STR_PAD_LEFT);
            $order->update(['code' => $code]);

            $total = 0.0;

            foreach ($lines as $line) {
                /** @var Product $product */
                $product = $line->product;

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $line->quantity,
                    'unit_price' => $line->unit_price,
                    'line_total' => $line->line_total,
                ]);

                $total += $line->line_total;
            }

            $total = round($total, 2);

            $order->update(['total' => $total]);
            $order->payment()->create([
                'method' => Payment::METHOD_COD,
                'amount' => $total,
                'status' => Payment::STATUS_PENDING,
            ]);

            $this->cart->clear();

            return $order->refresh()->load(['customer', 'items.product', 'payment', 'warehouse']);
        });
    }
}
