<?php

namespace App\Http\Requests\Store;

use App\Models\CustomerAddress;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CheckoutRequest extends FormRequest
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
        $customerId = $this->user()?->customer?->id;
        $usingSaved = $this->input('address_source') === 'saved';

        return [
            'address_source' => ['required', Rule::in(['saved', 'new'])],
            'address_id' => [
                Rule::requiredIf($usingSaved),
                'nullable',
                'integer',
                Rule::when(
                    $customerId !== null,
                    Rule::exists('customer_addresses', 'id')->where('customer_id', $customerId)
                ),
            ],
            'shipping_address' => [Rule::requiredIf(! $usingSaved), 'nullable', 'string', 'max:2000'],
            'shipping_city' => [Rule::requiredIf(! $usingSaved), 'nullable', 'string', 'max:100'],
            'shipping_pincode' => [Rule::requiredIf(! $usingSaved), 'nullable', 'string', 'max:10'],
            'address_label' => ['nullable', 'string', 'max:50'],
            'save_address' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('address_source') !== 'saved') {
                return;
            }

            $customer = $this->user()?->customer;
            if ($customer === null) {
                return;
            }

            $address = CustomerAddress::query()
                ->where('customer_id', $customer->id)
                ->whereKey($this->input('address_id'))
                ->first();

            if ($address === null) {
                $validator->errors()->add('address_id', 'Choose a saved delivery address.');
            }
        });
    }

    /**
     * @return array{
     *     shipping_address: string,
     *     shipping_city: string,
     *     shipping_pincode: string,
     *     notes: string|null,
     *     save_address: bool,
     *     address_label: string|null
     * }
     */
    public function shipping(): array
    {
        $data = $this->validated();
        $notes = $data['notes'] ?? null;

        if (($data['address_source'] ?? null) === 'saved') {
            $address = CustomerAddress::query()->findOrFail($data['address_id']);

            return [
                'shipping_address' => $address->address_line,
                'shipping_city' => $address->city,
                'shipping_pincode' => $address->pincode,
                'notes' => $notes,
                'save_address' => false,
                'address_label' => null,
            ];
        }

        return [
            'shipping_address' => $data['shipping_address'],
            'shipping_city' => $data['shipping_city'],
            'shipping_pincode' => $data['shipping_pincode'],
            'notes' => $notes,
            'save_address' => $this->boolean('save_address', true),
            'address_label' => $data['address_label'] ?? null,
        ];
    }
}
