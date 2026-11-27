<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductModelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'catalog_product_category_id' => ['required', 'integer', 'exists:catalog_product_categories,id'],
            'name' => ['required', 'string', 'max:100', Rule::unique('product_models', 'name')->where('catalog_product_category_id', $this->input('catalog_product_category_id'))->ignore($this->route('product_model'))],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in([ProductModel::STATUS_ACTIVE, ProductModel::STATUS_INACTIVE])],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('sort_order')) {
            $this->merge(['sort_order' => 0]);
        }
    }
}
