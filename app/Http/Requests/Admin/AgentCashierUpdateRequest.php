<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgentCashierUpdateRequest extends FormRequest
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
        $cashierId = $this->route('cashier')?->id;

        return [
            'cashier_id' => ['required', 'integer'],
            'cashier_form' => ['required', 'in:update'],
            'cashier_name' => ['required', 'string', 'max:150'],
            'cashier_email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($cashierId)],
            'cashier_phone' => ['nullable', 'string', 'max:20'],
            'cashier_password' => ['nullable', 'string', 'min:8'],
            'cashier_status' => ['required', 'in:active,inactive'],
        ];
    }
}
