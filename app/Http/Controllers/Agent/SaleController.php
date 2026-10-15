<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Exceptions\InsufficientStock;
use App\Http\Requests\Agent\SaleRequest;
use App\Models\AgentProductTerm;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Stock;
use App\Support\AgentSale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $agent = $request->user()->agent;

        $orders = Order::query()
            ->with(['customer', 'payment', 'items'])
            ->where('agent_id', $agent->id)
            ->orderByDesc('id')
            ->paginate(15);

        return view('agent.sales.index', [
            'orders' => $orders,
        ]);
    }

    public function create(Request $request): View
    {
        $agentId = $request->user()->agent->id;
        $stocks = Stock::query()
            ->where('agent_id', $agentId)
            ->where('quantity', '>', 0)
            ->pluck('quantity', 'product_id');

        $terms = AgentProductTerm::query()
            ->with('product')
            ->where('agent_id', $agentId)
            ->whereIn('product_id', $stocks->keys())
            ->whereHas('product', fn ($query) => $query->where('status', 'active'))
            ->get()
            ->sortBy(fn (AgentProductTerm $term) => $term->product?->name)
            ->values();

        return view('agent.sales.create', [
            'terms' => $terms,
            'stocks' => $stocks,
        ]);
    }

    public function lookupCustomer(Request $request): JsonResponse
    {
        $phone = trim((string) $request->query('phone', ''));

        if ($phone === '' || strlen($phone) > 20) {
            return response()->json([
                'found' => false,
            ]);
        }

        $customer = Customer::query()->where('phone', $phone)->first();

        if ($customer === null) {
            return response()->json([
                'found' => false,
            ]);
        }

        return response()->json([
            'found' => true,
            'name' => $customer->name,
            'email' => $customer->email,
            'active' => $customer->isActive(),
            'address' => $this->defaultAddress($customer),
        ]);
    }

    public function store(SaleRequest $request, AgentSale $sales): RedirectResponse
    {
        $agent = $request->user()->agent;
        $phone = $request->string('phone')->toString();
        $customer = Customer::query()->where('phone', $phone)->first();

        if ($customer === null) {
            $customer = Customer::query()->create([
                'name' => $request->string('customer_name')->toString(),
                'phone' => $phone,
                'email' => $request->input('email'),
                'status' => Customer::STATUS_ACTIVE,
            ]);
        }

        try {
            $order = $sales->complete(
                $agent,
                $customer,
                $request->lines(),
                $request->validated('method'),
                (int) $request->user()->id,
                $request->fulfilment(),
            );
        } catch (InsufficientStock|RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('agent.sales.show', $order)
            ->with('success', $order->code.' saved.');
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($order->agent_id === $request->user()->agent->id, 404);
        $order->load(['customer', 'agent', 'items.product', 'payment']);

        return view('agent.sales.show', [
            'order' => $order,
        ]);
    }

    /**
     * @return array{address_line: string, city: string, pincode: string}|null
     */
    private function defaultAddress(Customer $customer): ?array
    {
        $address = $customer->addresses()
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->first();

        if ($address === null) {
            return null;
        }

        return [
            'address_line' => $address->address_line,
            'city' => $address->city,
            'pincode' => $address->pincode,
        ];
    }
}
