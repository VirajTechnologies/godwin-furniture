<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\StoreCustomerAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class OrderController extends Controller
{
    public function __construct(private StoreCustomerAccount $accounts) {}

    public function index(): View|RedirectResponse
    {
        try {
            $customer = $this->accounts->ensureFor(request()->user());
        } catch (RuntimeException $exception) {
            return redirect()->route('store.home')->with('error', $exception->getMessage());
        }

        $orders = Order::query()
            ->with(['payment', 'items'])
            ->where('customer_id', $customer->id)
            ->where('channel', Order::CHANNEL_ONLINE)
            ->orderByDesc('id')
            ->paginate(10);

        return view('store.orders.index', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order): View
    {
        $this->authorizeOrder($order);

        $order->load(['customer', 'items.product.images', 'payment', 'warehouse']);

        return view('store.orders.show', [
            'order' => $order,
            'justPlaced' => session()->pull('just_placed', false),
        ]);
    }

    private function authorizeOrder(Order $order): void
    {
        if ($order->channel !== Order::CHANNEL_ONLINE) {
            abort(404);
        }

        try {
            $customer = $this->accounts->ensureFor(request()->user());
        } catch (RuntimeException) {
            abort(404);
        }

        if ($order->customer_id !== $customer->id) {
            abort(404);
        }
    }
}
