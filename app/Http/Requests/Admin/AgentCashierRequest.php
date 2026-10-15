<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgentCashierRequest extends FormRequest
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
            'cashier_name' => ['required', 'string', 'max:150'],
            'cashier_email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')],
            'cashier_phone' => ['nullable', 'string', 'max:20'],
            'cashier_password' => ['required', 'string', 'min:8'],
        ];
    }
}
