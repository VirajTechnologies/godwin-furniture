<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WarehouseStockRequest extends FormRequest
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
        if ($this->route('stock') instanceof Stock) {
            return [
                'direction' => ['required', Rule::in(['add', 'remove'])],
                'adjust_quantity' => ['required', 'integer', 'min:1'],
                'note' => ['required', 'string', 'max:2000'],
            ];
        }

        $rules = [
            'quantity' => ['required', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:2000'],
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')],
        ];

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $stock = $this->route('stock');

            if ($stock instanceof Stock) {
                if ($this->input('direction') === 'remove' && $this->integer('adjust_quantity') > $stock->quantity) {
                    $validator->errors()->add('adjust_quantity', 'Only '.$stock->quantity.' can be removed.');
                }

                return;
            }

            $product = Product::query()->find($this->integer('product_id'));
            $warehouse = Warehouse::query()->find($this->integer('warehouse_id'));

            if ($product && ! $product->isActive()) {
                $validator->errors()->add('product_id', 'Choose an active product.');
            }

            if ($warehouse && ! $warehouse->isActive()) {
                $validator->errors()->add('warehouse_id', 'Choose an active warehouse.');
            }

            if ($product && $warehouse && Stock::query()
                ->where('product_id', $product->id)
                ->where('warehouse_id', $warehouse->id)
                ->exists()) {
                $validator->errors()->add('product_id', 'This product already has stock in the selected warehouse.');
            }
        });
    }
}
