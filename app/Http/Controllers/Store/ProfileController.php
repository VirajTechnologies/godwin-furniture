<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\AddressRequest;
use App\Http\Requests\Store\UpdatePasswordRequest;
use App\Http\Requests\Store\UpdateProfileRequest;
use App\Models\CustomerAddress;
use App\Support\StoreCustomerAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class ProfileController extends Controller
{
    public function __construct(private StoreCustomerAccount $accounts) {}

    public function show(): View|RedirectResponse
    {
        try {
            $customer = $this->accounts->ensureFor(request()->user());
        } catch (RuntimeException $exception) {
            return redirect()->route('store.home')->with('error', $exception->getMessage());
        }

        $addresses = $customer->addresses()
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        return view('store.account', [
            'user' => request()->user(),
            'customer' => $customer,
            'addresses' => $addresses,
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        try {
            $this->accounts->updateProfile($request->user(), $request->profile());
        } catch (RuntimeException $exception) {
            return back()->withInput()->withErrors(['phone' => $exception->getMessage()]);
        }

        return redirect()->route('store.account')
            ->with('success', 'Account details updated.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $this->accounts->updatePassword($request->user(), $request->password());

        return redirect()->route('store.account')
            ->with('success', 'Password updated.');
    }

    public function storeAddress(AddressRequest $request): RedirectResponse
    {
        try {
            $customer = $this->accounts->ensureFor($request->user());
            $this->accounts->saveAddress($customer, $request->address());
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('store.account')
            ->with('success', 'Delivery address saved.');
    }

    public function makeDefaultAddress(CustomerAddress $address): RedirectResponse
    {
        try {
            $customer = $this->accounts->ensureFor(request()->user());
            $this->accounts->makeDefaultAddress($customer, $address);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('store.account')
            ->with('success', 'Default address updated.');
    }

    public function destroyAddress(CustomerAddress $address): RedirectResponse
    {
        try {
            $customer = $this->accounts->ensureFor(request()->user());
            $this->accounts->deleteAddress($customer, $address);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('store.account')
            ->with('success', 'Address removed.');
    }
}
