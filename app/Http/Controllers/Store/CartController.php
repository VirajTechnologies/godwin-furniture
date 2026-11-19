<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Support\StoreCart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private StoreCart $cart) {}

    public function show(): View
    {
        $lines = $this->cart->lines();

        return view('store.cart', [
            'lines' => $lines,
            'subtotal' => $this->cart->subtotal(),
            'itemCount' => $this->cart->count(),
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:'.StoreCart::MAX_QUANTITY],
        ]);

        $variant = ProductVariant::query()->with('product')->findOrFail($data['product_variant_id']);
        $this->cart->add($variant, $data['quantity'] ?? 1);

        if ($request->input('redirect') === 'cart') {
            return redirect()->route('store.cart')->with('success', $variant->displayName().' added to your bag.');
        }

        return back()->with('success', $variant->displayName().' added to your bag.');
    }

    public function update(Request $request, ProductVariant $variant): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:'.StoreCart::MAX_QUANTITY],
        ]);

        $this->cart->update($variant, $data['quantity']);

        return redirect()->route('store.cart')->with('success', 'Bag updated.');
    }

    public function remove(ProductVariant $variant): RedirectResponse
    {
        $this->cart->remove($variant);

        return redirect()->route('store.cart')->with('success', $variant->displayName().' removed from your bag.');
    }
}
