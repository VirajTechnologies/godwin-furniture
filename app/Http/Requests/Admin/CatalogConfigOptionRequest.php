<?php

namespace App\Http\Requests\Admin;

use App\Models\CatalogConfigOption;
use App\Support\Catalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CatalogConfigOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $familyKey = (string) $this->input('family_key');
        $fieldKey = (string) $this->input('field_key');
        $selectFields = Catalog::selectFieldsForFamily($familyKey);

        return [
            'family_key' => ['required', 'string', Rule::in(array_keys(config('catalog.families', [])))],
            'field_key' => ['required', 'string', Rule::in(array_keys($selectFields))],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('catalog_config_options', 'name')
                    ->where('family_key', $familyKey)
                    ->where('field_key', $fieldKey)
                    ->ignore($this->route('catalog_config_option')),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in([CatalogConfigOption::STATUS_ACTIVE, CatalogConfigOption::STATUS_INACTIVE])],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('sort_order')) {
            $this->merge(['sort_order' => 0]);
        }
    }
}
