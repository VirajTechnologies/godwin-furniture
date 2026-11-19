<?php

namespace App\Http\Requests\Agent;

use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAgentManager() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'integer', 'distinct', Rule::exists('product_variants', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            foreach ($this->input('items', []) as $index => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $variant = ProductVariant::query()->with('product')->find($item['product_variant_id'] ?? null);

                if ($variant && (! $variant->isActive() || ! $variant->product?->isActive())) {
                    $validator->errors()->add('items.'.$index.'.product_variant_id', 'Choose an active variant.');
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
            'items.*.product_variant_id.distinct' => 'Each variant can appear only once on a request.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
        ];
    }

    /**
     * @return list<array{product_variant_id: int, quantity: int}>
     */
    public function lines(): array
    {
        return collect($this->validated('items'))
            ->map(fn (array $item) => [
                'product_variant_id' => (int) $item['product_variant_id'],
                'quantity' => (int) $item['quantity'],
            ])
            ->values()
            ->all();
    }
}
