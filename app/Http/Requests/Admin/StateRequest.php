<?php

namespace App\Http\Requests\Admin;

use App\Models\State;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StateRequest extends FormRequest
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
            'state_name' => ['required', 'string', 'max:100', Rule::unique('states', 'state_name')->ignore($this->route('state'))],
            'status' => ['required', Rule::in([State::STATUS_ACTIVE, State::STATUS_INACTIVE])],
        ];
    }
}
