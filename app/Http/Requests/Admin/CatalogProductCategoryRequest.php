<?php

namespace App\Http\Requests\Admin;

use App\Models\CatalogProductCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CatalogProductCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'catalog_room_id' => ['required', 'integer', 'exists:catalog_rooms,id'],
            'name' => ['required', 'string', 'max:100', Rule::unique('catalog_product_categories', 'name')->where('catalog_room_id', $this->input('catalog_room_id'))->ignore($this->route('catalog_product_category'))],
            'slug' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in([CatalogProductCategory::STATUS_ACTIVE, CatalogProductCategory::STATUS_INACTIVE])],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('sort_order')) {
            $this->merge(['sort_order' => 0]);
        }
    }
}
