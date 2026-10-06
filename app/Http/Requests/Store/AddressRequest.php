<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
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
            'label' => ['nullable', 'string', 'max:50'],
            'address_line' => ['required', 'string', 'max:2000'],
            'city' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'string', 'max:10'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array{label: string|null, address_line: string, city: string, pincode: string, is_default: bool}
     */
    public function address(): array
    {
        $data = $this->validated();

        return [
            'label' => $data['label'] ?? null,
            'address_line' => $data['address_line'],
            'city' => $data['city'],
            'pincode' => $data['pincode'],
            'is_default' => $this->boolean('is_default'),
        ];
    }
}
