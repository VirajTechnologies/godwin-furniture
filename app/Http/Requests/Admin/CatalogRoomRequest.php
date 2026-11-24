<?php

namespace App\Http\Requests\Admin;

use App\Models\CatalogRoom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CatalogRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'project_category_id' => ['required', 'integer', 'exists:project_categories,id'],
            'name' => ['required', 'string', 'max:100', Rule::unique('catalog_rooms', 'name')->where('project_category_id', $this->input('project_category_id'))->ignore($this->route('catalog_room'))],
            'slug' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'show_on_store' => ['sometimes', 'boolean'],
            'status' => ['required', Rule::in([CatalogRoom::STATUS_ACTIVE, CatalogRoom::STATUS_INACTIVE])],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('sort_order')) {
            $this->merge(['sort_order' => 0]);
        }

        $this->merge([
            'show_on_store' => $this->boolean('show_on_store'),
        ]);
    }
}
