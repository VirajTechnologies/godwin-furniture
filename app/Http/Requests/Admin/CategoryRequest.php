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
            'menu_group_choice' => ['nullable', 'string', 'max:100'],
            'menu_group' => ['nullable', 'string', 'max:100'],
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

        return collect(parent::validated())->except('menu_group_choice')->all();
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('parent_id')) {
            $this->merge(['parent_id' => null, 'menu_group' => null, 'menu_group_choice' => null]);
        } else {
            $choice = $this->input('menu_group_choice');

            if ($choice === '__new__') {
                $this->merge(['menu_group' => $this->input('menu_group')]);
            } elseif ($this->filled('menu_group_choice')) {
                $this->merge(['menu_group' => $choice]);
            } else {
                $this->merge(['menu_group' => null]);
            }
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
            if ($this->filled('parent_id') && ! $this->filled('menu_group')) {
                $message = $this->input('menu_group_choice') === '__new__'
                    ? 'Enter a name for the new menu group.'
                    : 'Menu Group is required for a subcategory.';
                $validator->errors()->add(
                    $this->input('menu_group_choice') === '__new__' ? 'menu_group' : 'menu_group_choice',
                    $message
                );
            }

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
