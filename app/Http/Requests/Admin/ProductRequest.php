<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
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
        $product = $this->route('product');

        $rules = [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->whereNotNull('parent_id')),
            ],
            'name' => ['required', 'string', 'max:150'],
            'slug' => [
                'nullable',
                'string',
                'max:160',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('products', 'slug')->ignore($product),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'material' => ['nullable', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:30'],
            'selling_price' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'online_price' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'is_online' => ['required', 'boolean'],
            'is_featured' => ['required', 'boolean'],
            'status' => ['required', Rule::in([Product::STATUS_ACTIVE, Product::STATUS_INACTIVE])],
            'image_urls' => ['nullable', 'string', 'max:5000'],
            'branch_prices' => ['nullable', 'array'],
            'branch_prices.*' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
        ];

        if ($product === null) {
            $rules['code'] = ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9-]+$/', Rule::unique('products', 'code')];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_online' => $this->boolean('is_online'),
            'is_featured' => $this->boolean('is_featured'),
        ]);

        if ($this->filled('code')) {
            $this->merge([
                'code' => strtoupper((string) $this->input('code')),
            ]);
        }

        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge(['slug' => str($this->string('name'))->slug()->toString()]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $product = $this->route('product');
            $currentCategoryId = $product instanceof Product ? $product->category_id : null;
            $category = Category::query()->find($this->integer('category_id'));

            if ($category && ! $category->isSubcategory()) {
                $validator->errors()->add('category_id', 'Choose a subcategory, not a room.');
            }

            if ($category && ! $category->isActive() && $category->id !== $currentCategoryId) {
                $validator->errors()->add('category_id', 'Choose an active category.');
            }

            $mrp = $this->input('compare_at_price');
            $online = $this->input('online_price');
            $selling = $this->input('selling_price');

            if ($mrp !== null && $mrp !== '' && $online !== null && $online !== '' && (float) $mrp < (float) $online) {
                $validator->errors()->add('compare_at_price', 'MRP must be at least the online price.');
            }

            if ($mrp !== null && $mrp !== '' && $selling !== null && $selling !== '' && (float) $mrp < (float) $selling) {
                $validator->errors()->add('compare_at_price', 'MRP must be at least the selling price.');
            }
        });
    }

    /**
     * @return list<string>
     */
    public function imageUrlList(): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $this->input('image_urls', '')) ?: [])
            ->map(fn ($url) => trim($url))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.regex' => 'The code may contain only letters, numbers, and hyphens.',
            'slug.regex' => 'The slug may contain only lowercase letters, numbers, and hyphens.',
        ];
    }
}
