<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->whereNull('parent_id')),
                Rule::notIn([$category?->id]),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')
                    ->where(fn ($query) => $query->where('parent_id', $this->input('parent_id')))
                    ->ignore($category),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:120',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('categories', 'slug')->ignore($category),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in([Category::STATUS_ACTIVE, Category::STATUS_INACTIVE])],
        ];
    }

    /**
     * @return ($key is null ? array<string, mixed> : mixed)
     */
    public function validated($key = null, $default = null): mixed
    {
        if ($key !== null) {
            return parent::validated($key, $default);
        }

        return array_merge(parent::validated(), [
            'menu_group' => null,
        ]);
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('parent_id')) {
            $this->merge(['parent_id' => null]);
        }

        if (! $this->filled('sort_order')) {
            $this->merge(['sort_order' => 0]);
        }

        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge(['slug' => str($this->string('name'))->slug()->toString()]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $category = $this->route('category');
            if ($category instanceof Category && $category->children()->exists() && $this->filled('parent_id')) {
                $validator->errors()->add('parent_id', 'A category with subcategories cannot become a subcategory.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex' => 'The slug may contain only lowercase letters, numbers, and hyphens.',
        ];
    }
}
