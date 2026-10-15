<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockRequest;
use App\Models\StockTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function salesByBranch(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $rows = $this->places($from, $to);

        return view('admin.reports.sales-by-branch', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'rows' => $rows,
            'amount' => $rows->sum(fn ($row) => (float) $row->amount),
            'commission' => $rows->sum(fn ($row) => (float) $row->commission),
        ]);
    }

    public function salesByProduct(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $rows = OrderItem::query()
            ->selectRaw('product_id, SUM(quantity) as quantity, SUM(line_total) as amount, SUM(commission_amount) as commission')
            ->whereHas('order', function ($query) use ($from, $to): void {
                $query->whereIn('channel', [Order::CHANNEL_BRANCH, Order::CHANNEL_AGENT])
                    ->where('status', Order::STATUS_COMPLETED)
                    ->whereDate('created_at', '>=', $from->toDateString())
                    ->whereDate('created_at', '<=', $to->toDateString());
            })
            ->groupBy('product_id')
            ->orderByDesc('amount')
            ->get();

        $products = Product::query()->whereIn('id', $rows->pluck('product_id'))->get()->keyBy('id');

        return view('admin.reports.sales-by-product', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'rows' => $rows,
            'products' => $products,
        ]);
    }

    public function stock(): View
    {
        $stocks = Stock::query()
            ->with(['product', 'warehouse', 'branch', 'agent'])
            ->orderBy('warehouse_id')
            ->orderBy('branch_id')
            ->orderBy('product_id')
            ->get();

        return view('admin.reports.stock', [
            'stocks' => $stocks,
        ]);
    }

    public function transfers(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $transfers = StockTransfer::query()
            ->with(['branch', 'agent', 'warehouse'])
            ->whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString())
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.transfers', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'transfers' => $transfers,
        ]);
    }

    public function stockRequests(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $requests = StockRequest::query()
            ->with(['branch', 'agent', 'transfer'])
            ->whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString())
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.stock-requests', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'requests' => $requests,
        ]);
    }

    /**
     * @return Collection<int, object{kind: string, name: string, bills: int, amount: float, commission: float}>
     */
    private function places(Carbon $from, Carbon $to): Collection
    {
        $branchSales = Order::query()
            ->selectRaw('branch_id, COUNT(*) as bills, SUM(total) as amount')
            ->where('channel', Order::CHANNEL_BRANCH)
            ->where('status', Order::STATUS_COMPLETED)
            ->whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString())
            ->groupBy('branch_id')
            ->get();

        $shopSales = Order::query()
            ->selectRaw('agent_id, COUNT(*) as bills, SUM(total) as amount')
            ->where('channel', Order::CHANNEL_AGENT)
            ->where('status', Order::STATUS_COMPLETED)
            ->whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString())
            ->groupBy('agent_id')
            ->get();

        $branches = Branch::query()->whereIn('id', $branchSales->pluck('branch_id'))->get()->keyBy('id');
        $agents = Agent::query()->whereIn('id', $shopSales->pluck('agent_id'))->get()->keyBy('id');
        $branchCommission = $this->commissionBy('branch_id', Order::CHANNEL_BRANCH, $from, $to);
        $shopCommission = $this->commissionBy('agent_id', Order::CHANNEL_AGENT, $from, $to);

        return $branchSales->map(fn ($row) => (object) [
            'kind' => 'Showroom',
            'name' => $branches->get($row->branch_id)?->name ?? '—',
            'bills' => (int) $row->bills,
            'amount' => (float) $row->amount,
            'commission' => (float) ($branchCommission[$row->branch_id] ?? 0),
        ])->concat($shopSales->map(fn ($row) => (object) [
            'kind' => 'Shop',
            'name' => $agents->get($row->agent_id)?->name ?? '—',
            'bills' => (int) $row->bills,
            'amount' => (float) $row->amount,
            'commission' => (float) ($shopCommission[$row->agent_id] ?? 0),
        ]))->sortBy('name')->values();
    }

    /**
     * @return Collection<int|string, mixed>
     */
    private function commissionBy(string $column, string $channel, Carbon $from, Carbon $to): Collection
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.channel', $channel)
            ->where('orders.status', Order::STATUS_COMPLETED)
            ->whereDate('orders.created_at', '>=', $from->toDateString())
            ->whereDate('orders.created_at', '<=', $to->toDateString())
            ->whereNotNull('orders.'.$column)
            ->groupBy('orders.'.$column)
            ->selectRaw('orders.'.$column.' as place_id, SUM(order_items.commission_amount) as commission')
            ->pluck('commission', 'place_id');
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function range(Request $request): array
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $from = isset($data['from']) ? Carbon::parse($data['from'])->startOfDay() : now()->startOfMonth();
        $to = isset($data['to']) ? Carbon::parse($data['to'])->endOfDay() : now()->endOfDay();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }
}
