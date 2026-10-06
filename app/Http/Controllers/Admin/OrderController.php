<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InsufficientStock;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\OnlineOrderFulfilment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $channel = $request->string('channel')->toString();

        $orders = Order::query()
            ->with(['customer', 'branch', 'payment'])
            ->when($channel !== '' && in_array($channel, [Order::CHANNEL_ONLINE, Order::CHANNEL_BRANCH], true), function ($query) use ($channel) {
                $query->where('channel', $channel);
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'channel' => $channel,
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['customer', 'branch', 'warehouse', 'items.product', 'payment', 'seller']);

        return view('admin.orders.show', [
            'order' => $order,
        ]);
    }

    public function confirm(Order $order, OnlineOrderFulfilment $fulfilment): RedirectResponse
    {
        try {
            $fulfilment->confirm($order);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Order '.$order->code.' confirmed.');
    }

    public function dispatchOrder(Order $order, OnlineOrderFulfilment $fulfilment, Request $request): RedirectResponse
    {
        try {
            $fulfilment->dispatch($order, (int) $request->user()->id);
        } catch (InsufficientStock|RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Order '.$order->code.' dispatched. Warehouse stock updated.');
    }

    public function complete(Order $order, OnlineOrderFulfilment $fulfilment): RedirectResponse
    {
        try {
            $fulfilment->complete($order);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Order '.$order->code.' completed.');
    }
}
