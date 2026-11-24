<?php

namespace App\Http\Requests\Admin;

use App\Models\DesignType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DesignTypeRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100', Rule::unique('design_types', 'name')->where('catalog_product_category_id', $this->input('catalog_product_category_id'))->ignore($this->route('design_type'))],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in([DesignType::STATUS_ACTIVE, DesignType::STATUS_INACTIVE])],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('sort_order')) {
            $this->merge(['sort_order' => 0]);
        }
    }
}
