<?php

namespace App\Http\Controllers\Branch;

use App\Exceptions\InsufficientStock;
use App\Http\Controllers\Controller;
use App\Http\Requests\Branch\SaleRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Stock;
use App\Support\BranchSale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $branch = $request->user()->employee->branch;

        $orders = Order::query()
            ->with(['customer', 'payment'])
            ->where('branch_id', $branch->id)
            ->orderByDesc('id')
            ->paginate(15);

        return view('branch.sales.index', [
            'orders' => $orders,
        ]);
    }

    public function create(Request $request): View
    {
        return view('branch.sales.create', [
            'stocks' => $this->stocks($request),
        ]);
    }

    public function store(SaleRequest $request, BranchSale $sales): RedirectResponse
    {
        $branch = $request->user()->employee->branch;
        $phone = $request->string('phone')->toString();
        $customer = Customer::query()->where('phone', $phone)->first();

        if ($customer === null) {
            $customer = Customer::query()->create([
                'name' => $request->string('customer_name')->toString(),
                'phone' => $phone,
                'email' => $request->input('email'),
                'status' => Customer::STATUS_ACTIVE,
            ]);
        } else {
            $customer->update([
                'name' => $request->string('customer_name')->toString(),
                'email' => $request->input('email') ?: $customer->email,
            ]);
        }

        try {
            $order = $sales->complete(
                $branch,
                $customer,
                $request->lines(),
                $request->string('payment_method')->toString(),
                (int) $request->user()->id,
                $request->filled('notes') ? $request->string('notes')->toString() : null,
            );
        } catch (InsufficientStock $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('branch.sales.show', $order)
            ->with('success', 'Sale '.$order->code.' saved.');
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($order->branch_id === $request->user()->employee->branch_id, 404);

        $order->load(['customer', 'items.product', 'payment', 'seller']);

        return view('branch.sales.show', [
            'order' => $order,
        ]);
    }

    private function stocks(Request $request)
    {
        return Stock::query()
            ->with('product')
            ->where('branch_id', $request->user()->employee->branch_id)
            ->whereNull('warehouse_id')
            ->where('quantity', '>', 0)
            ->whereHas('product', function ($query): void {
                $query->where('status', Product::STATUS_ACTIVE);
            })
            ->get()
            ->sortBy(fn (Stock $stock) => $stock->product?->name)
            ->values();
    }
}
