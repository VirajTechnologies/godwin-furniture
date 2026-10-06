<?php

namespace App\Support;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StoreCustomerAccount
{
    /**
     * @param  array{name: string, phone: string, email: string, password: string}  $data
     */
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $phone = trim($data['phone']);

            $existingCustomer = Customer::query()->where('phone', $phone)->lockForUpdate()->first();

            if ($existingCustomer?->user_id) {
                throw new RuntimeException('This phone number is already registered. Please sign in.');
            }

            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $phone,
                'password' => $data['password'],
                'role_id' => null,
                'status' => 'active',
            ]);

            if ($existingCustomer) {
                if (! $existingCustomer->isActive()) {
                    throw new RuntimeException('This customer account is inactive. Please contact the store.');
                }

                $existingCustomer->update([
                    'user_id' => $user->id,
                    'name' => $data['name'],
                    'email' => $data['email'],
                ]);
            } else {
                Customer::query()->create([
                    'user_id' => $user->id,
                    'name' => $data['name'],
                    'phone' => $phone,
                    'email' => $data['email'],
                    'status' => Customer::STATUS_ACTIVE,
                ]);
            }

            return $user->fresh(['customer']);
        });
    }

    public function ensureFor(User $user): Customer
    {
        $user->loadMissing('customer');

        $customer = $user->customer;

        if ($customer) {
            if (! $customer->isActive()) {
                throw new RuntimeException('This customer account is inactive. Please contact the store.');
            }

            return $customer;
        }

        $phone = trim((string) $user->phone);

        if ($phone === '') {
            throw new RuntimeException('Add a phone number to your account before checkout.');
        }

        $byPhone = Customer::query()->where('phone', $phone)->first();

        if ($byPhone) {
            if ($byPhone->user_id && $byPhone->user_id !== $user->id) {
                throw new RuntimeException('This phone number is already linked to another account.');
            }

            if (! $byPhone->isActive()) {
                throw new RuntimeException('This customer account is inactive. Please contact the store.');
            }

            $byPhone->update([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email ?: $byPhone->email,
            ]);

            return $byPhone->fresh();
        }

        return Customer::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'phone' => $phone,
            'email' => $user->email,
            'status' => Customer::STATUS_ACTIVE,
        ]);
    }

    /**
     * @param  array{name: string, phone: string, email: string}  $data
     */
    public function updateProfile(User $user, array $data): Customer
    {
        return DB::transaction(function () use ($user, $data) {
            $customer = $this->ensureFor($user);
            $phone = trim($data['phone']);

            $phoneOwner = Customer::query()
                ->where('phone', $phone)
                ->whereKeyNot($customer->id)
                ->lockForUpdate()
                ->first();

            if ($phoneOwner !== null) {
                throw new RuntimeException('This phone number is already used by another customer.');
            }

            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $phone,
            ]);

            $customer->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $phone,
            ]);

            return $customer->fresh();
        });
    }

    public function updatePassword(User $user, string $password): void
    {
        $user->update([
            'password' => $password,
        ]);
    }

    /**
     * @param  array{label?: string|null, address_line: string, city: string, pincode: string, is_default?: bool}  $data
     */
    public function saveAddress(Customer $customer, array $data): CustomerAddress
    {
        return DB::transaction(function () use ($customer, $data) {
            $existing = CustomerAddress::query()
                ->where('customer_id', $customer->id)
                ->where('address_line', $data['address_line'])
                ->where('city', $data['city'])
                ->where('pincode', $data['pincode'])
                ->lockForUpdate()
                ->first();

            $makeDefault = (bool) ($data['is_default'] ?? false)
                || $customer->addresses()->count() === 0;

            if ($existing) {
                if ($makeDefault) {
                    $this->clearDefaultAddresses($customer->id);
                    $existing->update([
                        'label' => $data['label'] ?? $existing->label,
                        'is_default' => true,
                    ]);
                } elseif (($data['label'] ?? null) !== null) {
                    $existing->update(['label' => $data['label']]);
                }

                return $existing->fresh();
            }

            if ($makeDefault) {
                $this->clearDefaultAddresses($customer->id);
            }

            return CustomerAddress::query()->create([
                'customer_id' => $customer->id,
                'label' => $data['label'] ?? null,
                'address_line' => $data['address_line'],
                'city' => $data['city'],
                'pincode' => $data['pincode'],
                'is_default' => $makeDefault,
            ]);
        });
    }

    public function makeDefaultAddress(Customer $customer, CustomerAddress $address): void
    {
        if ($address->customer_id !== $customer->id) {
            throw new RuntimeException('Address not found.');
        }

        DB::transaction(function () use ($customer, $address) {
            $this->clearDefaultAddresses($customer->id);
            $address->update(['is_default' => true]);
        });
    }

    public function deleteAddress(Customer $customer, CustomerAddress $address): void
    {
        if ($address->customer_id !== $customer->id) {
            throw new RuntimeException('Address not found.');
        }

        DB::transaction(function () use ($customer, $address) {
            $wasDefault = $address->is_default;
            $address->delete();

            if ($wasDefault) {
                $next = CustomerAddress::query()
                    ->where('customer_id', $customer->id)
                    ->orderByDesc('id')
                    ->first();

                $next?->update(['is_default' => true]);
            }
        });
    }

    private function clearDefaultAddresses(int $customerId): void
    {
        CustomerAddress::query()
            ->where('customer_id', $customerId)
            ->update(['is_default' => false]);
    }
}
