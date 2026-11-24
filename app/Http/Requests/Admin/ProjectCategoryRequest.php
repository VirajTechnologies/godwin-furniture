<?php

namespace App\Http\Requests\Admin;

use App\Models\ProjectCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'business_group_id' => ['required', 'integer', 'exists:business_groups,id'],
            'name' => ['required', 'string', 'max:100', Rule::unique('project_categories', 'name')->where('business_group_id', $this->input('business_group_id'))->ignore($this->route('project_category'))],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'show_on_store' => ['sometimes', 'boolean'],
            'status' => ['required', Rule::in([ProjectCategory::STATUS_ACTIVE, ProjectCategory::STATUS_INACTIVE])],
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
