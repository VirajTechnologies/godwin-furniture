<?php

namespace App\Http\Requests\Admin;

use App\Models\District;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DistrictRequest extends FormRequest
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
            'district_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('districts', 'district_name')
                    ->where('state_id', $this->integer('state_id'))
                    ->ignore($this->route('district')),
            ],
            'status' => ['required', Rule::in([District::STATUS_ACTIVE, District::STATUS_INACTIVE])],
        ];
    }
}
