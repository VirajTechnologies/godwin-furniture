<?php

namespace App\Http\Requests\Admin;

use App\Models\Agent;
use App\Models\Branch;
use App\Models\Product;
use App\Models\StockTransfer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'destination' => ['required', Rule::in(['branch', 'agent'])],
            'branch_id' => ['required_if:destination,branch', 'nullable', 'integer', Rule::exists('branches', 'id')],
            'agent_id' => ['required_if:destination,agent', 'nullable', 'integer', Rule::exists('agents', 'id')],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', Rule::exists('products', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];

        if ($this->route('transfer') === null) {
            $rules['code'] = ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9-]+$/', Rule::unique('stock_transfers', 'code')];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('destination')) {
            $this->merge([
                'destination' => $this->filled('agent_id') ? 'agent' : 'branch',
            ]);
        }

        if ($this->filled('code')) {
            $this->merge([
                'code' => strtoupper((string) $this->input('code')),
            ]);
        }

        if ($this->input('destination') === 'agent') {
            $this->merge(['branch_id' => null]);
        }

        if ($this->input('destination') === 'branch') {
            $this->merge(['agent_id' => null]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $transfer = $this->route('transfer');
            $currentBranchId = $transfer instanceof StockTransfer ? $transfer->branch_id : null;
            $currentAgentId = $transfer instanceof StockTransfer ? $transfer->agent_id : null;
            $currentProductIds = $transfer instanceof StockTransfer
                ? $transfer->items()->pluck('product_id')->map(fn ($id) => (int) $id)->all()
                : [];

            if ($this->input('destination') === 'agent') {
                $agent = Agent::query()->with('warehouse')->find($this->integer('agent_id'));

                if ($agent && ! $agent->isActive() && $agent->id !== $currentAgentId) {
                    $validator->errors()->add('agent_id', 'Choose an active agent.');
                }

                if ($agent && (! $agent->warehouse || ! $agent->warehouse->isActive())) {
                    $validator->errors()->add('agent_id', 'The supplying warehouse is inactive.');
                }
            } else {
                $branch = Branch::query()->with('warehouse')->find($this->integer('branch_id'));

                if ($branch && ! $branch->isActive() && $branch->id !== $currentBranchId) {
                    $validator->errors()->add('branch_id', 'Choose an active branch.');
                }

                if ($branch && $branch->warehouse && ! $branch->warehouse->isActive()) {
                    $validator->errors()->add('branch_id', 'The supplying warehouse is inactive.');
                }
            }

            foreach ($this->input('items', []) as $index => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $product = Product::query()->find($item['product_id'] ?? null);

                if ($product && ! $product->isActive() && ! in_array($product->id, $currentProductIds, true)) {
                    $validator->errors()->add('items.'.$index.'.product_id', 'Choose an active product.');
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
            'code.regex' => 'The code may contain only letters, numbers, and hyphens.',
            'items.*.product_id.distinct' => 'Each product can appear only once on a transfer.',
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
