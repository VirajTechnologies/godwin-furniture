<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockRequest;
use App\Models\StockTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function salesByBranch(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $rows = Order::query()
            ->selectRaw('branch_id, COUNT(*) as bills, SUM(total) as amount')
            ->where('channel', Order::CHANNEL_BRANCH)
            ->where('status', Order::STATUS_COMPLETED)
            ->whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString())
            ->groupBy('branch_id')
            ->orderBy('branch_id')
            ->get();

        $branches = Branch::query()->whereIn('id', $rows->pluck('branch_id'))->get()->keyBy('id');

        return view('admin.reports.sales-by-branch', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'rows' => $rows,
            'branches' => $branches,
            'amount' => $rows->sum(fn ($row) => (float) $row->amount),
        ]);
    }

    public function salesByProduct(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $rows = OrderItem::query()
            ->selectRaw('product_id, SUM(quantity) as quantity, SUM(line_total) as amount')
            ->whereHas('order', function ($query) use ($from, $to): void {
                $query->where('channel', Order::CHANNEL_BRANCH)
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
            ->with(['product', 'warehouse', 'branch'])
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
            ->with(['branch', 'warehouse'])
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
            ->with(['branch', 'transfer'])
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
