<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->trim()->toString();

        $customers = Customer::query()
            ->with('user')
            ->withCount('orders')
            ->withSum('orders as orders_total', 'total')
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($builder) use ($q): void {
                    $builder->where('name', 'like', '%'.$q.'%')
                        ->orWhere('phone', 'like', '%'.$q.'%')
                        ->orWhere('email', 'like', '%'.$q.'%');
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.customers.index', [
            'customers' => $customers,
            'filters' => ['q' => $q],
        ]);
    }

    public function show(Customer $customer): View
    {
        $customer->load(['user', 'addresses']);

        $orders = Order::query()
            ->with(['branch', 'agent'])
            ->where('customer_id', $customer->id)
            ->orderByDesc('id')
            ->paginate(15);

        $summary = [
            'orders' => Order::query()->where('customer_id', $customer->id)->count(),
            'total' => (float) Order::query()->where('customer_id', $customer->id)->sum('total'),
            'online' => Order::query()->where('customer_id', $customer->id)->where('channel', Order::CHANNEL_ONLINE)->count(),
            'branch' => Order::query()->where('customer_id', $customer->id)->where('channel', Order::CHANNEL_BRANCH)->count(),
            'agent' => Order::query()->where('customer_id', $customer->id)->where('channel', Order::CHANNEL_AGENT)->count(),
        ];

        return view('admin.customers.show', [
            'customer' => $customer,
            'orders' => $orders,
            'summary' => $summary,
        ]);
    }
}
