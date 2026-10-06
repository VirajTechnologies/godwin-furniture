<?php

namespace App\Http\Requests\Store;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $phone = trim($this->string('phone')->toString());
            $customer = Customer::query()->where('phone', $phone)->first();

            if ($customer?->user_id) {
                $validator->errors()->add('phone', 'This phone number is already registered. Please sign in.');
            }

            if ($customer && ! $customer->isActive()) {
                $validator->errors()->add('phone', 'This customer account is inactive. Please contact the store.');
            }
        });
    }

    /**
     * @return array{name: string, phone: string, email: string, password: string}
     */
    public function registration(): array
    {
        $data = $this->validated();

        return [
            'name' => $data['name'],
            'phone' => trim($data['phone']),
            'email' => $data['email'],
            'password' => $data['password'],
        ];
    }
}
