<?php

namespace App\Http\Requests\Admin;

use App\Models\BusinessGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BusinessGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('business_groups', 'name')->ignore($this->route('business_group'))],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in([BusinessGroup::STATUS_ACTIVE, BusinessGroup::STATUS_INACTIVE])],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('sort_order')) {
            $this->merge(['sort_order' => 0]);
        }
    }
}
