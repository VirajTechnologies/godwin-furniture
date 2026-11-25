<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductUpholstery;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductUpholsteryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('product_upholsteries', 'name')->ignore($this->route('product_upholstery'))],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in([ProductUpholstery::STATUS_ACTIVE, ProductUpholstery::STATUS_INACTIVE])],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('sort_order')) {
            $this->merge(['sort_order' => 0]);
        }
    }
}
