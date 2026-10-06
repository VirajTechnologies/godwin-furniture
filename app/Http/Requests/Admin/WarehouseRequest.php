<?php

namespace App\Http\Requests\Admin;

use App\Models\City;
use App\Models\District;
use App\Models\State;
use App\Models\Warehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WarehouseRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'address_line' => ['required', 'string', 'max:5000'],
            'state_id' => ['required', 'integer', Rule::exists('states', 'id')],
            'district_id' => ['required', 'integer', Rule::exists('districts', 'id')],
            'city_id' => ['required', 'integer', Rule::exists('cities', 'id')],
            'pincode' => ['required', 'string', 'max:10'],
            'status' => ['required', Rule::in([Warehouse::STATUS_ACTIVE, Warehouse::STATUS_INACTIVE])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];

        if ($this->route('warehouse') === null) {
            $rules['code'] = ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9-]+$/', Rule::unique('warehouses', 'code')];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('code')) {
            $this->merge([
                'code' => strtoupper((string) $this->input('code')),
            ]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $warehouse = $this->route('warehouse');

            $currentStateId = $warehouse instanceof Warehouse ? $warehouse->state_id : null;
            $currentDistrictId = $warehouse instanceof Warehouse ? $warehouse->district_id : null;
            $currentCityId = $warehouse instanceof Warehouse ? $warehouse->city_id : null;

            $state = State::query()->find($this->integer('state_id'));
            $district = District::query()->find($this->integer('district_id'));
            $city = City::query()->find($this->integer('city_id'));

            if ($state && ! $state->isActive() && $state->id !== $currentStateId) {
                $validator->errors()->add('state_id', 'Choose an active state.');
            }

            if ($district && $state && $district->state_id !== $state->id) {
                $validator->errors()->add('district_id', 'The district does not belong to the selected state.');
            } elseif ($district && ! $district->isActive() && $district->id !== $currentDistrictId) {
                $validator->errors()->add('district_id', 'Choose an active district.');
            }

            if ($city && $district && ($city->district_id !== $district->id || $city->state_id !== $this->integer('state_id'))) {
                $validator->errors()->add('city_id', 'The city does not belong to the selected district.');
            } elseif ($city && ! $city->isActive() && $city->id !== $currentCityId) {
                $validator->errors()->add('city_id', 'Choose an active city.');
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
