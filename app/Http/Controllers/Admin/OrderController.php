<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::query()
            ->with(['customer', 'branch', 'payment'])
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.orders.index', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['customer', 'branch', 'items.product', 'payment', 'seller']);

        return view('admin.orders.show', [
            'order' => $order,
        ]);
    }
}
