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
        $rules = [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:5000'],
            'unit' => ['required', 'string', 'max:30'],
            'selling_price' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'is_online' => ['required', 'boolean'],
            'status' => ['required', Rule::in([Product::STATUS_ACTIVE, Product::STATUS_INACTIVE])],
        ];

        if ($this->route('product') === null) {
            $rules['code'] = ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9-]+$/', Rule::unique('products', 'code')];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_online' => $this->boolean('is_online'),
        ]);

        if ($this->filled('code')) {
            $this->merge([
                'code' => strtoupper((string) $this->input('code')),
            ]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $product = $this->route('product');
            $currentCategoryId = $product instanceof Product ? $product->category_id : null;
            $category = Category::query()->find($this->integer('category_id'));

            if ($category && ! $category->isActive() && $category->id !== $currentCategoryId) {
                $validator->errors()->add('category_id', 'Choose an active category.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.regex' => 'The code may contain only letters, numbers, and hyphens.',
        ];
    }
}
