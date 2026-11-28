<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockRequest;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $this->resolveFilter($request);
        $branches = Branch::query()->where('status', Branch::STATUS_ACTIVE)->orderBy('name')->get();
        $warehouses = Warehouse::query()->where('status', Warehouse::STATUS_ACTIVE)->orderBy('name')->get();

        $onlineOrders = $this->onlineOrdersQuery($filter);
        $pendingOnline = (clone $onlineOrders)->where('status', '!=', Order::STATUS_COMPLETED);
        $pendingDeliveries = (clone $onlineOrders)
            ->where('status', '!=', Order::STATUS_COMPLETED)
            ->where(function (Builder $query): void {
                $query->where('status', Order::STATUS_DISPATCHED)
                    ->orWhere('delivery_type', Order::DELIVERY_DELIVERY);
            });

        $stockQuery = $this->stockQuery($filter);

        // Reference: Total Revenue = showroom/agent invoices + ecommerce order amounts.
        $offlineCompleted = Order::query()
            ->whereIn('channel', [Order::CHANNEL_BRANCH, Order::CHANNEL_AGENT])
            ->where('status', Order::STATUS_COMPLETED)
            ->when($filter['type'] === 'branch', fn (Builder $q) => $q->where('branch_id', $filter['id']))
            ->when($filter['type'] === 'warehouse', fn (Builder $q) => $q->where('warehouse_id', $filter['id']));

        $totalRevenue = (float) (clone $offlineCompleted)->sum('total')
            + (float) (clone $onlineOrders)->sum('total');

        // Reference: Total Sales = invoice count + ecommerce order count.
        $totalSalesCount = (clone $offlineCompleted)->count() + (clone $onlineOrders)->count();

        return view('admin.dashboard', [
            'filter' => $filter,
            'filterLabel' => $this->filterLabel($filter, $branches, $warehouses),
            'branches' => $branches,
            'warehouses' => $warehouses,
            'monthlyRevenueTitle' => 'Monthly revenue ('.now()->format('M').' = live)',

            // KPIs — reference order & labels
            'totalRevenue' => $totalRevenue,
            'totalSalesCount' => $totalSalesCount,
            'totalOrders' => (clone $onlineOrders)->count(),
            'totalProducts' => Product::query()->count(),
            'totalStock' => (int) (clone $stockQuery)->sum('quantity'),
            'totalCustomers' => Customer::query()->count(),
            'pendingOrders' => (clone $pendingOnline)->count(),
            'pendingDeliveries' => (clone $pendingDeliveries)->count(),

            // Charts — reference order
            'monthlyRevenue' => $this->monthlyRevenue($filter),
            'branchSales' => $this->branchSalesBars($filter, $branches),
            'locationStock' => $this->locationStockBars($filter, $branches, $warehouses),
            'channelMix' => $this->channelMix($filter),
            'topProducts' => $this->topProducts($filter),
            'orderStatusBars' => $this->orderStatusBars($filter),

            // Detail cards — branches then warehouses
            'branchCards' => $this->branchCards($filter, $branches),
            'warehouseCards' => $this->warehouseCards($filter, $warehouses),

            // Bottom row (reference)
            'recentTransactions' => $this->recentTransactions($filter),
            'recentActivity' => $this->recentActivity($filter),

            // Essential ops (after reference rows)
            'waitingTransfers' => StockTransfer::query()
                ->with(['branch', 'agent', 'warehouse'])
                ->where('status', StockTransfer::STATUS_DISPATCHED)
                ->when($filter['type'] === 'branch', fn (Builder $q) => $q->where('branch_id', $filter['id']))
                ->when($filter['type'] === 'warehouse', fn (Builder $q) => $q->where('warehouse_id', $filter['id']))
                ->orderBy('dispatched_at')
                ->limit(8)
                ->get(),
            'openRequests' => StockRequest::query()
                ->with(['branch', 'agent'])
                ->where('status', StockRequest::STATUS_REQUESTED)
                ->whereNull('stock_transfer_id')
                ->when($filter['type'] === 'branch', fn (Builder $q) => $q->where('branch_id', $filter['id']))
                ->orderBy('id')
                ->limit(8)
                ->get(),
            'openCustomRequests' => CustomRequest::query()
                ->with(['agent', 'branch', 'product'])
                ->where('status', CustomRequest::STATUS_REQUESTED)
                ->when($filter['type'] === 'branch', fn (Builder $q) => $q->where('branch_id', $filter['id']))
                ->orderBy('id')
                ->limit(8)
                ->get(),
            'draftTransferCount' => StockTransfer::query()
                ->where('status', StockTransfer::STATUS_DRAFT)
                ->when($filter['type'] === 'branch', fn (Builder $q) => $q->where('branch_id', $filter['id']))
                ->when($filter['type'] === 'warehouse', fn (Builder $q) => $q->where('warehouse_id', $filter['id']))
                ->count(),
        ]);
    }

    /**
     * @return array{type: string, id: int|null, key: string}
     */
    private function resolveFilter(Request $request): array
    {
        $key = $request->string('location')->toString();

        if (preg_match('/^branch:(\d+)$/', $key, $matches)) {
            $id = (int) $matches[1];
            if (Branch::query()->whereKey($id)->exists()) {
                return ['type' => 'branch', 'id' => $id, 'key' => "branch:{$id}"];
            }
        }

        if (preg_match('/^warehouse:(\d+)$/', $key, $matches)) {
            $id = (int) $matches[1];
            if (Warehouse::query()->whereKey($id)->exists()) {
                return ['type' => 'warehouse', 'id' => $id, 'key' => "warehouse:{$id}"];
            }
        }

        return ['type' => 'all', 'id' => null, 'key' => 'all'];
    }

    /**
     * @param  array{type: string, id: int|null, key: string}  $filter
     * @param  Collection<int, Branch>  $branches
     * @param  Collection<int, Warehouse>  $warehouses
     */
    private function filterLabel(array $filter, Collection $branches, Collection $warehouses): string
    {
        return match ($filter['type']) {
            'branch' => $branches->firstWhere('id', $filter['id'])?->name ?? 'Branch',
            'warehouse' => $warehouses->firstWhere('id', $filter['id'])?->name ?? 'Warehouse',
            default => 'Overall Business',
        };
    }

    /**
     * @param  array{type: string, id: int|null}  $filter
     */
    private function completedSalesQuery(array $filter): Builder
    {
        return Order::query()
            ->whereIn('channel', [Order::CHANNEL_BRANCH, Order::CHANNEL_AGENT, Order::CHANNEL_ONLINE])
            ->where('status', Order::STATUS_COMPLETED)
            ->when($filter['type'] === 'branch', fn (Builder $q) => $q->where('branch_id', $filter['id']))
            ->when($filter['type'] === 'warehouse', fn (Builder $q) => $q->where('warehouse_id', $filter['id']));
    }

    /**
     * @param  array{type: string, id: int|null}  $filter
     */
    private function onlineOrdersQuery(array $filter): Builder
    {
        return Order::query()
            ->where('channel', Order::CHANNEL_ONLINE)
            ->when($filter['type'] === 'branch', fn (Builder $q) => $q->where('branch_id', $filter['id']))
            ->when($filter['type'] === 'warehouse', fn (Builder $q) => $q->where('warehouse_id', $filter['id']));
    }

    /**
     * @param  array{type: string, id: int|null}  $filter
     */
    private function stockQuery(array $filter): Builder
    {
        return Stock::query()
            ->when($filter['type'] === 'branch', fn (Builder $q) => $q->where('branch_id', $filter['id']))
            ->when($filter['type'] === 'warehouse', fn (Builder $q) => $q->where('warehouse_id', $filter['id']));
    }

    /**
     * @param  array{type: string, id: int|null}  $filter
     * @return list<array{label: string, value: float}>
     */
    private function monthlyRevenue(array $filter): array
    {
        $start = now()->startOfMonth()->subMonths(5);
        $rows = $this->completedSalesQuery($filter)
            ->where('created_at', '>=', $start)
            ->get(['created_at', 'total']);

        $buckets = [];
        for ($i = 0; $i < 6; $i++) {
            $month = $start->copy()->addMonths($i);
            $buckets[$month->format('Y-m')] = [
                'label' => $month->format('M Y'),
                'value' => 0.0,
            ];
        }

        foreach ($rows as $row) {
            $key = $row->created_at?->format('Y-m');
            if ($key !== null && isset($buckets[$key])) {
                $buckets[$key]['value'] += (float) $row->total;
            }
        }

        return array_values($buckets);
    }

    /**
     * @param  array{type: string, id: int|null}  $filter
     * @param  Collection<int, Branch>  $branches
     * @return list<array{label: string, value: float}>
     */
    private function branchSalesBars(array $filter, Collection $branches): array
    {
        $scope = $filter['type'] === 'branch'
            ? $branches->where('id', $filter['id'])
            : $branches;

        $amounts = Order::query()
            ->where('channel', Order::CHANNEL_BRANCH)
            ->where('status', Order::STATUS_COMPLETED)
            ->when($filter['type'] === 'branch', fn (Builder $q) => $q->where('branch_id', $filter['id']))
            ->selectRaw('branch_id, SUM(total) as amount')
            ->groupBy('branch_id')
            ->pluck('amount', 'branch_id');

        return $scope->map(fn (Branch $branch) => [
            'label' => $branch->name,
            'value' => (float) ($amounts[$branch->id] ?? 0),
        ])->values()->all();
    }

    /**
     * @param  array{type: string, id: int|null}  $filter
     * @param  Collection<int, Branch>  $branches
     * @param  Collection<int, Warehouse>  $warehouses
     * @return list<array{label: string, value: int}>
     */
    private function locationStockBars(array $filter, Collection $branches, Collection $warehouses): array
    {
        $bars = [];

        $warehouseScope = $filter['type'] === 'warehouse'
            ? $warehouses->where('id', $filter['id'])
            : ($filter['type'] === 'branch' ? collect() : $warehouses);

        $warehouseQty = Stock::query()
            ->whereNotNull('warehouse_id')
            ->selectRaw('warehouse_id, SUM(quantity) as qty')
            ->groupBy('warehouse_id')
            ->pluck('qty', 'warehouse_id');

        foreach ($warehouseScope as $warehouse) {
            $bars[] = [
                'label' => $warehouse->name,
                'value' => (int) ($warehouseQty[$warehouse->id] ?? 0),
            ];
        }

        $branchScope = $filter['type'] === 'branch'
            ? $branches->where('id', $filter['id'])
            : ($filter['type'] === 'warehouse' ? collect() : $branches);

        $branchQty = Stock::query()
            ->whereNotNull('branch_id')
            ->selectRaw('branch_id, SUM(quantity) as qty')
            ->groupBy('branch_id')
            ->pluck('qty', 'branch_id');

        foreach ($branchScope as $branch) {
            $bars[] = [
                'label' => $branch->name,
                'value' => (int) ($branchQty[$branch->id] ?? 0),
            ];
        }

        if ($filter['type'] === 'all') {
            $agentQty = (int) Stock::query()->whereNotNull('agent_id')->sum('quantity');
            if ($agentQty > 0 || Agent::query()->exists()) {
                $bars[] = ['label' => 'All shops', 'value' => $agentQty];
            }
        }

        return $bars;
    }

    /**
     * @param  array{type: string, id: int|null}  $filter
     * @return list<array{label: string, value: float}>
     */
    private function channelMix(array $filter): array
    {
        $showroom = Order::query()
            ->where('channel', Order::CHANNEL_BRANCH)
            ->where('status', Order::STATUS_COMPLETED)
            ->when($filter['type'] === 'branch', fn (Builder $q) => $q->where('branch_id', $filter['id']))
            ->when($filter['type'] === 'warehouse', fn (Builder $q) => $q->where('warehouse_id', $filter['id']));

        $agent = Order::query()
            ->where('channel', Order::CHANNEL_AGENT)
            ->where('status', Order::STATUS_COMPLETED)
            ->when($filter['type'] === 'branch', fn (Builder $q) => $q->where('branch_id', $filter['id']))
            ->when($filter['type'] === 'warehouse', fn (Builder $q) => $q->where('warehouse_id', $filter['id']));

        return [
            [
                'label' => 'Online',
                'value' => (float) $this->onlineOrdersQuery($filter)->sum('total'),
            ],
            [
                'label' => 'Showroom',
                'value' => (float) $showroom->sum('total'),
            ],
            [
                'label' => 'Agent (B2B)',
                'value' => (float) $agent->sum('total'),
            ],
        ];
    }

    /**
     * @param  array{type: string, id: int|null}  $filter
     * @return list<array{label: string, value: int}>
     */
    private function topProducts(array $filter): array
    {
        $orderIds = $this->completedSalesQuery($filter)->select('id');

        return OrderItem::query()
            ->whereIn('order_id', $orderIds)
            ->selectRaw('product_id, SUM(quantity) as qty')
            ->groupBy('product_id')
            ->orderByDesc('qty')
            ->limit(5)
            ->with('product:id,name,code')
            ->get()
            ->map(fn (OrderItem $item) => [
                'label' => $item->product?->name ?? ('#'.$item->product_id),
                'value' => (int) $item->qty,
            ])
            ->all();
    }

    /**
     * @param  array{type: string, id: int|null}  $filter
     * @return list<array{label: string, value: int}>
     */
    private function orderStatusBars(array $filter): array
    {
        $base = $this->onlineOrdersQuery($filter);

        return [
            [
                'label' => 'Placed / Confirmed',
                'value' => (clone $base)->whereIn('status', [Order::STATUS_PLACED, Order::STATUS_CONFIRMED])->count(),
            ],
            [
                'label' => 'Dispatched',
                'value' => (clone $base)->where('status', Order::STATUS_DISPATCHED)->count(),
            ],
            [
                'label' => 'Completed',
                'value' => (clone $base)->where('status', Order::STATUS_COMPLETED)->count(),
            ],
        ];
    }

    /**
     * @param  array{type: string, id: int|null}  $filter
     * @param  Collection<int, Branch>  $branches
     * @return list<array{name: string, sales: int, revenue: float, stock: int, units: int, pendingBalance: float}>
     */
    private function branchCards(array $filter, Collection $branches): array
    {
        $scope = $filter['type'] === 'branch'
            ? $branches->where('id', $filter['id'])
            : ($filter['type'] === 'warehouse' ? collect() : $branches);

        return $scope->map(function (Branch $branch) {
            $orders = Order::query()
                ->where('channel', Order::CHANNEL_BRANCH)
                ->where('branch_id', $branch->id)
                ->where('status', Order::STATUS_COMPLETED);

            $orderIds = (clone $orders)->pluck('id');
            $revenue = (float) (clone $orders)->sum('total');
            $paidByOrder = Payment::query()
                ->whereIn('order_id', $orderIds)
                ->where('status', Payment::STATUS_PAID)
                ->selectRaw('order_id, SUM(amount) as paid')
                ->groupBy('order_id')
                ->pluck('paid', 'order_id');
            $pendingBalanceCount = (clone $orders)->get(['id', 'total'])
                ->filter(fn (Order $order) => ((float) $order->total - (float) ($paidByOrder[$order->id] ?? 0)) > 0.009)
                ->count();

            return [
                'name' => $branch->name,
                'sales' => (clone $orders)->count(),
                'revenue' => $revenue,
                'stock' => (int) Stock::query()->where('branch_id', $branch->id)->sum('quantity'),
                'units' => (int) OrderItem::query()->whereIn('order_id', $orderIds)->sum('quantity'),
                'pendingBalance' => $pendingBalanceCount,
            ];
        })->values()->all();
    }

    /**
     * @param  array{type: string, id: int|null}  $filter
     * @param  Collection<int, Warehouse>  $warehouses
     * @return list<array{name: string, stock: int, incoming: int, outgoing: int, pendingTransfers: int}>
     */
    private function warehouseCards(array $filter, Collection $warehouses): array
    {
        $scope = $filter['type'] === 'warehouse'
            ? $warehouses->where('id', $filter['id'])
            : ($filter['type'] === 'branch' ? collect() : $warehouses);

        return $scope->map(function (Warehouse $warehouse) {
            $pending = StockTransfer::query()
                ->where('warehouse_id', $warehouse->id)
                ->whereIn('status', [StockTransfer::STATUS_DRAFT, StockTransfer::STATUS_DISPATCHED]);

            $outgoing = (int) StockTransferItem::query()
                ->whereHas('transfer', fn (Builder $q) => $q
                    ->where('warehouse_id', $warehouse->id)
                    ->where('status', StockTransfer::STATUS_DISPATCHED))
                ->sum('quantity');

            $incoming = (int) StockTransferItem::query()
                ->whereHas('transfer', fn (Builder $q) => $q
                    ->where('warehouse_id', $warehouse->id)
                    ->where('status', StockTransfer::STATUS_RETURNED))
                ->sum('returned_quantity');

            return [
                'name' => $warehouse->name,
                'stock' => (int) Stock::query()->where('warehouse_id', $warehouse->id)->sum('quantity'),
                'incoming' => $incoming,
                'outgoing' => $outgoing,
                'pendingTransfers' => (clone $pending)->count(),
            ];
        })->values()->all();
    }

    /**
     * Reference columns: Invoice, Branch, Amount.
     *
     * @param  array{type: string, id: int|null}  $filter
     * @return list<array{invoice: string, branch: string, amount: float, url: string}>
     */
    private function recentTransactions(array $filter): array
    {
        return Order::query()
            ->with(['branch', 'agent'])
            ->whereIn('channel', [Order::CHANNEL_BRANCH, Order::CHANNEL_AGENT, Order::CHANNEL_ONLINE])
            ->when($filter['type'] === 'branch', fn (Builder $q) => $q->where('branch_id', $filter['id']))
            ->when($filter['type'] === 'warehouse', fn (Builder $q) => $q->where('warehouse_id', $filter['id']))
            ->orderByDesc('id')
            ->limit(5)
            ->get()
            ->map(fn (Order $order) => [
                'invoice' => $order->code,
                'branch' => $order->branch?->name
                    ?? $order->agent?->name
                    ?? ($order->isOnline() ? 'Online' : '—'),
                'amount' => (float) $order->total,
                'url' => route('admin.orders.show', $order),
            ])
            ->all();
    }

    /**
     * Lightweight activity feed from transfers, requests, and recent orders.
     *
     * @param  array{type: string, id: int|null}  $filter
     * @return list<string>
     */
    private function recentActivity(array $filter): array
    {
        $lines = collect();

        $transfers = StockTransfer::query()
            ->with(['branch', 'agent', 'warehouse'])
            ->when($filter['type'] === 'branch', fn (Builder $q) => $q->where('branch_id', $filter['id']))
            ->when($filter['type'] === 'warehouse', fn (Builder $q) => $q->where('warehouse_id', $filter['id']))
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();

        foreach ($transfers as $transfer) {
            $lines->push([
                'at' => $transfer->updated_at,
                'text' => sprintf(
                    '%s · Transfer %s → %s (%s)',
                    $transfer->updated_at?->format('H:i') ?? '',
                    $transfer->code,
                    $transfer->destinationName(),
                    ucfirst($transfer->status)
                ),
            ]);
        }

        $requests = StockRequest::query()
            ->with(['branch', 'agent'])
            ->where('status', StockRequest::STATUS_REQUESTED)
            ->whereNull('stock_transfer_id')
            ->when($filter['type'] === 'branch', fn (Builder $q) => $q->where('branch_id', $filter['id']))
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        foreach ($requests as $request) {
            $lines->push([
                'at' => $request->created_at,
                'text' => sprintf(
                    '%s · Stock request %s from %s',
                    $request->created_at?->format('H:i') ?? '',
                    $request->code,
                    $request->holderName()
                ),
            ]);
        }

        $customRequests = CustomRequest::query()
            ->with('agent')
            ->where('status', CustomRequest::STATUS_REQUESTED)
            ->when($filter['type'] === 'branch', fn (Builder $q) => $q->where('branch_id', $filter['id']))
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        foreach ($customRequests as $customRequest) {
            $lines->push([
                'at' => $customRequest->created_at,
                'text' => sprintf(
                    '%s · Custom request %s from %s',
                    $customRequest->created_at?->format('H:i') ?? '',
                    $customRequest->code,
                    $customRequest->agent?->name ?? 'Agent'
                ),
            ]);
        }

        $orders = Order::query()
            ->when($filter['type'] === 'branch', fn (Builder $q) => $q->where('branch_id', $filter['id']))
            ->when($filter['type'] === 'warehouse', fn (Builder $q) => $q->where('warehouse_id', $filter['id']))
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        foreach ($orders as $order) {
            $lines->push([
                'at' => $order->created_at,
                'text' => sprintf(
                    '%s · Order %s · ₹%s',
                    $order->created_at?->format('H:i') ?? '',
                    $order->code,
                    number_format((float) $order->total, 2)
                ),
            ]);
        }

        return $lines
            ->sortByDesc(fn (array $row) => $row['at']?->timestamp ?? 0)
            ->take(6)
            ->map(fn (array $row) => $row['text'])
            ->values()
            ->all();
    }
}
