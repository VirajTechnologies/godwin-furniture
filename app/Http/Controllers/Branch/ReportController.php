<?php

namespace App\Http\Controllers\Branch;

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
    public function salesByProduct(Request $request): View
    {
        $branch = $this->branch($request);
        [$from, $to] = $this->range($request);

        $rows = OrderItem::query()
            ->selectRaw('product_id, SUM(quantity) as quantity, SUM(line_total) as amount')
            ->whereHas('order', function ($query) use ($branch, $from, $to): void {
                $query->where('branch_id', $branch->id)
                    ->where('channel', Order::CHANNEL_BRANCH)
                    ->where('status', Order::STATUS_COMPLETED)
                    ->whereDate('created_at', '>=', $from->toDateString())
                    ->whereDate('created_at', '<=', $to->toDateString());
            })
            ->groupBy('product_id')
            ->orderByDesc('amount')
            ->get();

        return view('branch.reports.sales-by-product', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'rows' => $rows,
            'products' => Product::query()->whereIn('id', $rows->pluck('product_id'))->get()->keyBy('id'),
        ]);
    }

    public function stock(Request $request): View
    {
        $branch = $this->branch($request);

        return view('branch.reports.stock', [
            'stocks' => Stock::query()
                ->with('product')
                ->where('branch_id', $branch->id)
                ->orderBy('product_id')
                ->get(),
        ]);
    }

    public function transfers(Request $request): View
    {
        $branch = $this->managerBranch($request);
        [$from, $to] = $this->range($request);

        $transfers = StockTransfer::query()
            ->with('warehouse')
            ->where('branch_id', $branch->id)
            ->whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString())
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('branch.reports.transfers', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'transfers' => $transfers,
        ]);
    }

    public function stockRequests(Request $request): View
    {
        $branch = $this->managerBranch($request);
        [$from, $to] = $this->range($request);

        $requests = StockRequest::query()
            ->with('transfer')
            ->where('branch_id', $branch->id)
            ->whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString())
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('branch.reports.stock-requests', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'requests' => $requests,
        ]);
    }

    private function branch(Request $request): Branch
    {
        $user = $request->user();
        $user?->loadMissing('role', 'employee.branch');
        $branch = $user?->employee?->branch;

        abort_unless(
            $user?->isBranchUser()
            && $user->status === 'active'
            && $user->employee?->isActive()
            && $branch instanceof Branch
            && $branch->isActive(),
            403,
        );

        return $branch;
    }

    private function managerBranch(Request $request): Branch
    {
        $branch = $this->branch($request);

        abort_unless($request->user()?->isBranchManager(), 403);

        return $branch;
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
