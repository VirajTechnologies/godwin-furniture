<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductColour;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductColourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('product_colours', 'name')->ignore($this->route('product_colour'))],
            'hex' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in([ProductColour::STATUS_ACTIVE, ProductColour::STATUS_INACTIVE])],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('sort_order')) {
            $this->merge(['sort_order' => 0]);
        }

        if ($this->filled('hex')) {
            $this->merge(['hex' => strtolower((string) $this->input('hex'))]);
        }
    }
}
