<?php

namespace App\Http\Requests\Branch;

use App\Models\Customer;
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
        return [
            'customer_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'payment_method' => ['required', Rule::in([Payment::METHOD_CASH, Payment::METHOD_UPI, Payment::METHOD_CARD])],
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
}
