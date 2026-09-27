<?php

namespace App\Http\Requests\Admin;

use App\Models\City;
use App\Models\District;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CityRequest extends FormRequest
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
        return [
            'state_id' => ['required', 'integer', Rule::exists('states', 'id')],
            'district_id' => ['required', 'integer', Rule::exists('districts', 'id')],
            'city_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('cities', 'city_name')
                    ->where('district_id', $this->integer('district_id'))
                    ->ignore($this->route('city')),
            ],
            'status' => ['required', Rule::in([City::STATUS_ACTIVE, City::STATUS_INACTIVE])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $district = District::query()->find($this->integer('district_id'));

            if ($district && $district->state_id !== $this->integer('state_id')) {
                $validator->errors()->add('district_id', 'The district does not belong to the selected state.');
            }
        });
    }
}
