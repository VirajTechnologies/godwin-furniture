<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\CheckoutRequest;
use App\Support\OnlineCheckout;
use App\Support\StoreCart;
use App\Support\StoreCustomerAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        private StoreCart $cart,
        private StoreCustomerAccount $accounts,
    ) {}

    public function show(): View|RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('store.cart')
                ->with('error', 'Your bag is empty. Add furniture before checkout.');
        }

        try {
            $customer = $this->accounts->ensureFor(request()->user());
        } catch (RuntimeException $exception) {
            return redirect()->route('store.cart')->with('error', $exception->getMessage());
        }

        $addresses = $customer->addresses()
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        $lines = $this->cart->lines();

        return view('store.checkout', [
            'customer' => $customer,
            'addresses' => $addresses,
            'lines' => $lines,
            'subtotal' => $this->cart->subtotal(),
            'itemCount' => $this->cart->count(),
        ]);
    }

    public function store(CheckoutRequest $request, OnlineCheckout $checkout): RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('store.cart')
                ->with('error', 'Your bag is empty. Add furniture before checkout.');
        }

        try {
            $order = $checkout->place($request->user(), $request->shipping());
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('store.orders.show', $order)
            ->with('success', 'Order '.$order->code.' placed successfully.')
            ->with('just_placed', true);
    }
}
