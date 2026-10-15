<?php

namespace App\Http\Requests\Admin;

use App\Models\Agent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgentRequest extends FormRequest
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
        $agent = $this->route('agent');
        $userId = $agent instanceof Agent ? $agent->user_id : null;

        $rules = [
            'name' => ['required', 'string', 'max:150'],
            'warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($userId)],
            'password' => [$agent instanceof Agent ? 'nullable' : 'required', 'string', 'min:8'],
            'address_line' => ['required', 'string', 'max:2000'],
            'state_id' => ['required', 'integer', Rule::exists('states', 'id')],
            'district_id' => ['required', 'integer', Rule::exists('districts', 'id')],
            'city_id' => ['required', 'integer', Rule::exists('cities', 'id')],
            'pincode' => ['required', 'string', 'max:10'],
            'status' => ['required', Rule::in([Agent::STATUS_ACTIVE, Agent::STATUS_INACTIVE])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];

        if (! $agent instanceof Agent) {
            $rules['code'] = ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9-]+$/', Rule::unique('agents', 'code')];
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
