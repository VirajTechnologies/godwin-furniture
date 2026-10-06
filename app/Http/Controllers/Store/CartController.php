<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
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
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:'.StoreCart::MAX_QUANTITY],
        ]);

        $product = Product::query()->findOrFail($data['product_id']);
        $this->cart->add($product, $data['quantity'] ?? 1);

        if ($request->input('redirect') === 'cart') {
            return redirect()->route('store.cart')->with('success', $product->name.' added to your bag.');
        }

        return back()->with('success', $product->name.' added to your bag.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:'.StoreCart::MAX_QUANTITY],
        ]);

        $this->cart->update($product, $data['quantity']);

        return redirect()->route('store.cart')->with('success', 'Bag updated.');
    }

    public function remove(Product $product): RedirectResponse
    {
        $this->cart->remove($product);

        return redirect()->route('store.cart')->with('success', $product->name.' removed from your bag.');
    }
}
