<?php

namespace App\Http\Requests\Branch;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isBranchUser() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $isDelivery = $this->input('delivery_type') === Order::DELIVERY_DELIVERY;

        return [
            'customer_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'payment_method' => ['required', Rule::in([Payment::METHOD_CASH, Payment::METHOD_UPI, Payment::METHOD_CARD])],
            'delivery_type' => ['required', Rule::in([Order::DELIVERY_PICKUP, Order::DELIVERY_DELIVERY])],
            'shipping_address' => [Rule::requiredIf($isDelivery), 'nullable', 'string', 'max:2000'],
            'shipping_city' => [Rule::requiredIf($isDelivery), 'nullable', 'string', 'max:100'],
            'shipping_pincode' => [Rule::requiredIf($isDelivery), 'nullable', 'string', 'max:10'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', Rule::exists('products', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $branchId = $this->user()?->employee?->branch_id;
            $customer = Customer::query()->where('phone', $this->string('phone')->toString())->first();

            if ($customer && ! $customer->isActive()) {
                $validator->errors()->add('phone', 'This customer is inactive.');
            }

            foreach ($this->input('items', []) as $index => $item) {
                if (! is_array($item) || $branchId === null) {
                    continue;
                }

                $product = Product::query()->find($item['product_id'] ?? null);

                if ($product && ! $product->isActive()) {
                    $validator->errors()->add('items.'.$index.'.product_id', 'Choose an active product.');

                    continue;
                }

                $available = (int) Stock::query()
                    ->where('product_id', $product?->id)
                    ->where('branch_id', $branchId)
                    ->whereNull('warehouse_id')
                    ->value('quantity');

                $quantity = (int) ($item['quantity'] ?? 0);

                if ($product && $quantity > $available) {
                    $validator->errors()->add('items.'.$index.'.quantity', $product->name.' has '.$available.' at this branch.');
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.*.product_id.distinct' => 'Each product can appear only once on a sale.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'shipping_address.required' => 'Enter the delivery address for door delivery.',
            'shipping_city.required' => 'Enter the city for door delivery.',
            'shipping_pincode.required' => 'Enter the pincode for door delivery.',
        ];
    }

    /**
     * @return list<array{product_id: int, quantity: int}>
     */
    public function lines(): array
    {
        return collect($this->validated('items'))
            ->map(fn (array $item) => [
                'product_id' => (int) $item['product_id'],
                'quantity' => (int) $item['quantity'],
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{
     *     delivery_type: string,
     *     shipping_address: string|null,
     *     shipping_city: string|null,
     *     shipping_pincode: string|null,
     *     notes: string|null
     * }
     */
    public function fulfilment(): array
    {
        $data = $this->validated();

        return [
            'delivery_type' => $data['delivery_type'],
            'shipping_address' => $data['shipping_address'] ?? null,
            'shipping_city' => $data['shipping_city'] ?? null,
            'shipping_pincode' => $data['shipping_pincode'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];
    }
}
