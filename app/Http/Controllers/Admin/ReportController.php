<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Branch;
use App\Models\CustomRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\StockRequest;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function overview(Request $request): View
    {
        [$from, $to] = $this->range($request);
        $data = $this->overviewData($from, $to);

        return view('admin.reports.overview', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'branches' => $data['branches'],
            'warehouses' => $data['warehouses'],
            'products' => $data['products'],
        ]);
    }

    public function exportOverview(Request $request): StreamedResponse
    {
        [$from, $to] = $this->range($request);
        $data = $this->overviewData($from, $to);

        $filename = 'reports-overview-'.$from->toDateString().'-'.$to->toDateString().'.csv';

        return response()->streamDownload(function () use ($data): void {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['Branch-wise']);
            fputcsv($out, ['Branch', 'Sales', 'Revenue', 'Stock', 'Customers']);
            foreach ($data['branches'] as $row) {
                fputcsv($out, [$row->name, $row->sales, number_format($row->revenue, 2, '.', ''), $row->stock, $row->customers]);
            }

            fputcsv($out, []);
            fputcsv($out, ['Warehouse-wise']);
            fputcsv($out, ['Warehouse', 'Stock', 'Pending dispatch']);
            foreach ($data['warehouses'] as $row) {
                fputcsv($out, [$row->name, $row->stock, $row->pending_dispatch]);
            }

            fputcsv($out, []);
            fputcsv($out, ['Product-wise']);
            fputcsv($out, ['Product', 'Sold', 'Revenue', 'Stock', 'Movement']);
            foreach ($data['products'] as $row) {
                fputcsv($out, [$row->name, $row->sold, number_format($row->revenue, 2, '.', ''), $row->stock, $row->movement]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

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
            ->selectRaw('product_variant_id, SUM(quantity) as quantity, SUM(line_total) as amount, SUM(commission_amount) as commission')
            ->whereHas('order', function ($query) use ($from, $to): void {
                $query->whereIn('channel', [Order::CHANNEL_BRANCH, Order::CHANNEL_AGENT])
                    ->where('status', Order::STATUS_COMPLETED)
                    ->whereDate('created_at', '>=', $from->toDateString())
                    ->whereDate('created_at', '<=', $to->toDateString());
            })
            ->whereNotNull('product_variant_id')
            ->groupBy('product_variant_id')
            ->orderByDesc('amount')
            ->get();

        $variants = ProductVariant::query()
            ->with('product')
            ->whereIn('id', $rows->pluck('product_variant_id'))
            ->get()
            ->keyBy('id');

        return view('admin.reports.sales-by-product', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'rows' => $rows,
            'variants' => $variants,
        ]);
    }

    public function stock(): View
    {
        $stocks = Stock::query()
            ->with(['variant.product', 'warehouse', 'branch', 'agent'])
            ->orderBy('warehouse_id')
            ->orderBy('branch_id')
            ->orderBy('product_variant_id')
            ->get();

        $warehouseQty = (int) Stock::query()->whereNotNull('warehouse_id')->sum('quantity');
        $branchQty = (int) Stock::query()->whereNotNull('branch_id')->sum('quantity');
        $agentQty = (int) Stock::query()->whereNotNull('agent_id')->sum('quantity');
        $onHandTotal = $warehouseQty + $branchQty + $agentQty;
        $inTransitQty = (int) StockTransferItem::query()
            ->whereHas('transfer', fn ($query) => $query->where('status', StockTransfer::STATUS_DISPATCHED))
            ->sum('quantity');

        return view('admin.reports.stock', [
            'stocks' => $stocks,
            'summary' => [
                'warehouses' => $warehouseQty,
                'branches' => $branchQty,
                'agents' => $agentQty,
                'on_hand' => $onHandTotal,
                'in_transit' => $inTransitQty,
                'matched_total' => $onHandTotal + $inTransitQty,
            ],
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

    public function customRequests(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $requests = CustomRequest::query()
            ->with(['agent', 'branch', 'product'])
            ->whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString())
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.custom-requests', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'requests' => $requests,
        ]);
    }

    /**
     * @return array{
     *     branches: Collection<int, object{name: string, sales: int, revenue: float, stock: int, customers: int}>,
     *     warehouses: Collection<int, object{name: string, stock: int, pending_dispatch: int}>,
     *     products: Collection<int, object{name: string, sold: int, revenue: float, stock: int, movement: string}>
     * }
     */
    private function overviewData(Carbon $from, Carbon $to): array
    {
        return [
            'branches' => $this->branchWiseOverview($from, $to),
            'warehouses' => $this->warehouseWiseOverview(),
            'products' => $this->productWiseOverview($from, $to),
        ];
    }

    /**
     * @return Collection<int, object{name: string, sales: int, revenue: float, stock: int, customers: int}>
     */
    private function branchWiseOverview(Carbon $from, Carbon $to): Collection
    {
        $branchSales = Order::query()
            ->selectRaw('branch_id, COUNT(*) as sales, COALESCE(SUM(total), 0) as revenue, COUNT(DISTINCT customer_id) as customers')
            ->where('channel', Order::CHANNEL_BRANCH)
            ->where('status', Order::STATUS_COMPLETED)
            ->whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString())
            ->whereNotNull('branch_id')
            ->groupBy('branch_id')
            ->get()
            ->keyBy('branch_id');

        $agentSales = Order::query()
            ->selectRaw('agent_id, COUNT(*) as sales, COALESCE(SUM(total), 0) as revenue, COUNT(DISTINCT customer_id) as customers')
            ->where('channel', Order::CHANNEL_AGENT)
            ->where('status', Order::STATUS_COMPLETED)
            ->whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString())
            ->whereNotNull('agent_id')
            ->groupBy('agent_id')
            ->get()
            ->keyBy('agent_id');

        $branchStock = Stock::query()
            ->selectRaw('branch_id, COALESCE(SUM(quantity), 0) as stock')
            ->whereNotNull('branch_id')
            ->groupBy('branch_id')
            ->pluck('stock', 'branch_id');

        $agentStock = Stock::query()
            ->selectRaw('agent_id, COALESCE(SUM(quantity), 0) as stock')
            ->whereNotNull('agent_id')
            ->groupBy('agent_id')
            ->pluck('stock', 'agent_id');

        $branchIds = $branchSales->keys()->merge($branchStock->keys())->unique()->filter();
        $agentIds = $agentSales->keys()->merge($agentStock->keys())->unique()->filter();

        $branches = Branch::query()->whereIn('id', $branchIds)->get()->keyBy('id');
        $agents = Agent::query()->whereIn('id', $agentIds)->get()->keyBy('id');

        return $branchIds->map(function ($id) use ($branches, $branchSales, $branchStock) {
            $sale = $branchSales->get($id);

            return (object) [
                'name' => $branches->get($id)?->name ?? '—',
                'sales' => (int) ($sale->sales ?? 0),
                'revenue' => (float) ($sale->revenue ?? 0),
                'stock' => (int) ($branchStock[$id] ?? 0),
                'customers' => (int) ($sale->customers ?? 0),
            ];
        })->concat($agentIds->map(function ($id) use ($agents, $agentSales, $agentStock) {
            $sale = $agentSales->get($id);

            return (object) [
                'name' => $agents->get($id)?->name ?? '—',
                'sales' => (int) ($sale->sales ?? 0),
                'revenue' => (float) ($sale->revenue ?? 0),
                'stock' => (int) ($agentStock[$id] ?? 0),
                'customers' => (int) ($sale->customers ?? 0),
            ];
        }))->sortBy('name')->values();
    }

    /**
     * @return Collection<int, object{name: string, stock: int, pending_dispatch: int}>
     */
    private function warehouseWiseOverview(): Collection
    {
        $stockByWarehouse = Stock::query()
            ->selectRaw('warehouse_id, COALESCE(SUM(quantity), 0) as stock')
            ->whereNotNull('warehouse_id')
            ->groupBy('warehouse_id')
            ->pluck('stock', 'warehouse_id');

        $pendingByWarehouse = Order::query()
            ->selectRaw('warehouse_id, COUNT(*) as pending')
            ->where('channel', Order::CHANNEL_ONLINE)
            ->whereIn('status', [Order::STATUS_PLACED, Order::STATUS_CONFIRMED])
            ->whereNotNull('warehouse_id')
            ->groupBy('warehouse_id')
            ->pluck('pending', 'warehouse_id');

        $warehouseIds = Warehouse::query()
            ->orderBy('name')
            ->pluck('id')
            ->merge($stockByWarehouse->keys())
            ->merge($pendingByWarehouse->keys())
            ->unique()
            ->filter();

        $warehouses = Warehouse::query()->whereIn('id', $warehouseIds)->get()->keyBy('id');

        return $warehouseIds->map(fn ($id) => (object) [
            'name' => $warehouses->get($id)?->name ?? '—',
            'stock' => (int) ($stockByWarehouse[$id] ?? 0),
            'pending_dispatch' => (int) ($pendingByWarehouse[$id] ?? 0),
        ])->sortBy('name')->values();
    }

    /**
     * @return Collection<int, object{name: string, sold: int, revenue: float, stock: int, movement: string}>
     */
    private function productWiseOverview(Carbon $from, Carbon $to): Collection
    {
        $sales = OrderItem::query()
            ->selectRaw('product_id, COALESCE(SUM(quantity), 0) as sold, COALESCE(SUM(line_total), 0) as revenue')
            ->whereHas('order', function ($query) use ($from, $to): void {
                $query->where('status', Order::STATUS_COMPLETED)
                    ->whereDate('created_at', '>=', $from->toDateString())
                    ->whereDate('created_at', '<=', $to->toDateString());
            })
            ->whereNotNull('product_id')
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $stockByProduct = Stock::query()
            ->join('product_variants', 'product_variants.id', '=', 'stocks.product_variant_id')
            ->selectRaw('product_variants.product_id, COALESCE(SUM(stocks.quantity), 0) as stock')
            ->groupBy('product_variants.product_id')
            ->pluck('stock', 'product_id');

        $productIds = $sales->keys()->merge($stockByProduct->keys())->unique()->filter();
        $products = Product::query()->whereIn('id', $productIds)->get()->keyBy('id');

        return $productIds->map(function ($id) use ($products, $sales, $stockByProduct) {
            $sale = $sales->get($id);
            $sold = (int) ($sale->sold ?? 0);

            return (object) [
                'name' => $products->get($id)?->name ?? '—',
                'sold' => $sold,
                'revenue' => (float) ($sale->revenue ?? 0),
                'stock' => (int) ($stockByProduct[$id] ?? 0),
                'movement' => $sold > 3 ? 'Best seller' : 'Slow',
            ];
        })->sortByDesc('revenue')->values();
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
