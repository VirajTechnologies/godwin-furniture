<?php

namespace App\Http\Requests\Agent;

use App\Models\CustomRequest;
use App\Support\Catalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CustomRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAgentManager() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $matched = ! $this->boolean('model_not_listed');
        $upholstery = Catalog::usesUpholstery(
            Catalog::family((string) $this->input('project_category'), (string) $this->input('product_category'))
        );

        $rules = [
            'model_not_listed' => ['sometimes', 'boolean'],
            'project_category' => ['nullable', 'string', 'max:80'],
            'room' => ['nullable', 'string', 'max:80'],
            'product_category' => ['nullable', 'string', 'max:100'],
            'design_type' => ['nullable', 'string', 'max:100'],
            'model_name' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:5000'],
            'customer_name' => ['nullable', 'string', 'max:150'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['required', 'string', 'max:5000'],
            'width_cm' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'depth_cm' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'height_cm' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'weight_kg' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'material' => ['nullable', 'string', 'max:100'],
            'specs' => ['nullable', 'array'],
            'specs.*' => ['nullable', 'string', 'max:120'],
            'variants' => ['nullable', 'array'],
            'variants.*.colour' => ['nullable', 'string', 'max:60'],
            'variants.*.finish' => ['nullable', 'string', 'max:60'],
            'variants.*.upholstery' => ['nullable', 'string', 'max:60'],
        ];

        if ($matched) {
            $rules['project_category'] = ['required', 'string', Rule::in(Catalog::projects())];
            $rules['room'] = ['required', 'string', 'max:80'];
            $rules['product_category'] = ['required', 'string', 'max:100'];
            $rules['design_type'] = ['required', 'string', 'max:100'];
            $rules['model_name'] = ['required', 'string', 'max:150'];
            $rules['variants'] = ['required', 'array', 'min:1'];
            $rules['variants.*.colour'] = ['required', 'string', Rule::in(array_keys(Catalog::colours()))];
            $rules['variants.*.finish'] = ['required', 'string', Rule::in(Catalog::finishes())];
            $rules['variants.*.upholstery'] = $upholstery
                ? ['required', 'string', Rule::in(Catalog::upholsteries())]
                : ['nullable', 'string', 'max:60'];
        } else {
            $rules['model_name'] = ['required', 'string', 'max:150'];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'model_not_listed' => $this->boolean('model_not_listed'),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->boolean('model_not_listed')) {
                return;
            }

            $project = (string) $this->input('project_category');
            $room = (string) $this->input('room');
            $category = (string) $this->input('product_category');
            $type = (string) $this->input('design_type');
            $model = (string) $this->input('model_name');

            $rooms = Catalog::rooms($project);
            if ($room !== '' && ! in_array($room, $rooms, true)) {
                $validator->errors()->add('room', 'Choose a room from the list.');
            }

            $categories = $rooms === [] ? [] : (Catalog::tree()[$project][$room] ?? []);
            if ($category !== '' && ! in_array($category, $categories, true)) {
                $validator->errors()->add('product_category', 'Choose a product category from the list.');
            }

            $types = Catalog::typesFor($category);
            if ($type !== '' && ! in_array($type, $types, true)) {
                $validator->errors()->add('design_type', 'Choose a design type from the list.');
            }

            $models = Catalog::modelsFor($category);
            if ($model !== '' && ! in_array($model, $models, true)) {
                $validator->errors()->add('model_name', 'Choose a model from the catalog, or mark that it is not in the catalog.');
            }
        });
    }

    /**
     * @return array{
     *     match_mode: string,
     *     project_category: ?string,
     *     room: ?string,
     *     product_category: ?string,
     *     design_type: ?string,
     *     model_name: string,
     *     description: ?string,
     *     width_cm: ?float,
     *     depth_cm: ?float,
     *     height_cm: ?float,
     *     weight_kg: ?float,
     *     material: ?string,
     *     base_price: null,
     *     specs: array<string, string>,
     *     variants: list<array{colour: string, finish: string, upholstery: string}>,
     *     customer_name: ?string,
     *     customer_phone: ?string,
     *     notes: string
     * }
     */
    public function payload(): array
    {
        $matched = ! $this->boolean('model_not_listed');
        $data = $this->validated();

        $variants = [];
        foreach ($data['variants'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }

            $variants[] = [
                'colour' => (string) ($row['colour'] ?? ''),
                'finish' => (string) ($row['finish'] ?? ''),
                'upholstery' => (string) ($row['upholstery'] ?? ''),
            ];
        }

        $specs = [];
        foreach ($data['specs'] ?? [] as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $specs[(string) $key] = (string) $value;
        }

        return [
            'match_mode' => $matched ? CustomRequest::MODE_MATCHED : CustomRequest::MODE_UNMATCHED,
            'project_category' => $data['project_category'] ?? null,
            'room' => $data['room'] ?? null,
            'product_category' => $data['product_category'] ?? null,
            'design_type' => $data['design_type'] ?? null,
            'model_name' => (string) $data['model_name'],
            'description' => filled($data['description'] ?? null) ? (string) $data['description'] : null,
            'width_cm' => isset($data['width_cm']) ? (float) $data['width_cm'] : null,
            'depth_cm' => isset($data['depth_cm']) ? (float) $data['depth_cm'] : null,
            'height_cm' => isset($data['height_cm']) ? (float) $data['height_cm'] : null,
            'weight_kg' => isset($data['weight_kg']) ? (float) $data['weight_kg'] : null,
            'material' => $data['material'] ?? null,
            'base_price' => null,
            'specs' => $specs,
            'variants' => $matched ? $variants : [],
            'customer_name' => $data['customer_name'] ?? null,
            'customer_phone' => $data['customer_phone'] ?? null,
            'notes' => (string) $data['notes'],
        ];
    }
}
