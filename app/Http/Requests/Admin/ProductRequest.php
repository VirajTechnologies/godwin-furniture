<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\Catalog;
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
        $upholstery = Catalog::usesUpholstery($this->family());

        $rules = [
            'project_category' => ['required', 'string', Rule::in(Catalog::projects())],
            'room' => ['required', 'string', 'max:80'],
            'product_category' => ['required', 'string', 'max:100'],
            'design_type' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:150'],
            'slug' => [
                'nullable',
                'string',
                'max:160',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('products', 'slug')->ignore($product),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'width_cm' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'depth_cm' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'height_cm' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'weight_kg' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'specs' => ['nullable', 'array'],
            'specs.*' => ['nullable', 'string', 'max:120'],
            'material' => ['nullable', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:30'],
            'base_price' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'is_online' => ['required', 'boolean'],
            'is_featured' => ['required', 'boolean'],
            'status' => ['required', Rule::in([Product::STATUS_ACTIVE, Product::STATUS_INACTIVE])],
            'image_urls' => ['nullable', 'string', 'max:5000'],

            'variants' => ['required', 'array', 'min:1'],
            'variants.*.colour' => ['required', 'string', Rule::in(array_keys(Catalog::colours()))],
            'variants.*.finish' => ['required', 'string', Rule::in(Catalog::finishes())],
            'variants.*.upholstery' => $upholstery
                ? ['required', 'string', Rule::in(Catalog::upholsteries())]
                : ['nullable', 'string', 'max:60'],
            'variants.*.price' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'variants.*.compare_at_price' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'variants.*.status' => ['required', Rule::in([ProductVariant::STATUS_ACTIVE, ProductVariant::STATUS_INACTIVE])],

            'branch_prices' => ['nullable', 'array'],
            'branch_prices.*' => ['nullable', 'array'],
            'branch_prices.*.*' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'agent_terms' => ['nullable', 'array'],
            'agent_terms.*' => ['nullable', 'array'],
            'agent_terms.*.*.price' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'agent_terms.*.*.commission_percent' => ['nullable', 'numeric', 'min:0', 'max:100', 'decimal:0,2'],
        ];

        if ($product === null) {
            $rules['code'] = ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9-]+$/', Rule::unique('products', 'code')];
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
            $this->merge(['code' => strtoupper((string) $this->input('code'))]);
        }

        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge(['slug' => str($this->string('name'))->slug()->toString()]);
        }

        $variants = $this->input('variants');

        if (is_array($variants)) {
            $this->merge(['variants' => array_values(array_filter(
                $variants,
                fn ($row) => is_array($row) && ($row['colour'] ?? '') !== '' && ($row['finish'] ?? '') !== '',
            ))]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $project = (string) $this->input('project_category');
            $room = (string) $this->input('room');
            $category = (string) $this->input('product_category');

            if ($project !== '' && $room !== '' && ! in_array($room, Catalog::rooms($project), true)) {
                $validator->errors()->add('room', 'Choose a room from this project category.');
            } elseif ($project !== '' && $room !== '' && $category !== '' && ! Catalog::pathExists($project, $room, $category)) {
                $validator->errors()->add('product_category', 'Choose a product category from this room.');
            }

            $seen = [];

            foreach ($this->input('variants', []) as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }

                $price = $row['price'] ?? null;
                $mrp = $row['compare_at_price'] ?? null;

                if ($mrp !== null && $mrp !== '' && $price !== null && $price !== '' && (float) $mrp < (float) $price) {
                    $validator->errors()->add('variants.'.$index.'.compare_at_price', 'MRP must be at least the price.');
                }

                $key = ($row['colour'] ?? '').'|'.($row['finish'] ?? '').'|'.($row['upholstery'] ?? '');

                if (isset($seen[$key])) {
                    $validator->errors()->add('variants.'.$index.'.colour', 'This combination is listed twice.');

                    continue;
                }

                $seen[$key] = true;
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
            'variants.required' => 'Generate at least one variant.',
            'variants.min' => 'Generate at least one variant.',
            'variants.*.upholstery.required' => 'Choose an upholstery for this variant.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'project_category' => 'project category',
            'product_category' => 'product category',
            'design_type' => 'type',
            'name' => 'model',
            'base_price' => 'base price',
        ];
    }

    private function family(): string
    {
        return Catalog::family(
            (string) $this->input('project_category'),
            (string) $this->input('product_category'),
        );
    }
}
