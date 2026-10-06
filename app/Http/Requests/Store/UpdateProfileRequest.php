<?php

namespace App\Http\Requests\Store;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($this->user()->id),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $phone = trim($this->string('phone')->toString());
            $customerId = $this->user()?->customer?->id;

            $taken = Customer::query()
                ->where('phone', $phone)
                ->when($customerId, fn ($query) => $query->whereKeyNot($customerId))
                ->exists();

            if ($taken) {
                $validator->errors()->add('phone', 'This phone number is already used by another customer.');
            }
        });
    }

    /**
     * @return array{name: string, phone: string, email: string}
     */
    public function profile(): array
    {
        $data = $this->validated();

        return [
            'name' => $data['name'],
            'phone' => trim($data['phone']),
            'email' => $data['email'],
        ];
    }
}
