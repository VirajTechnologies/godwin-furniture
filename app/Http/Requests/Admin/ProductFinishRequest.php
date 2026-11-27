<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductFinish;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductFinishRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('product_finishes', 'name')->ignore($this->route('product_finish'))],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in([ProductFinish::STATUS_ACTIVE, ProductFinish::STATUS_INACTIVE])],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('sort_order')) {
            $this->merge(['sort_order' => 0]);
        }
    }
}
