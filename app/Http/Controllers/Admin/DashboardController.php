<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\StockRequest;
use App\Models\StockTransfer;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = Order::query()
            ->where('channel', Order::CHANNEL_BRANCH)
            ->where('status', Order::STATUS_COMPLETED)
            ->whereDate('created_at', today());

        $byBranch = (clone $today)
            ->selectRaw('branch_id, COUNT(*) as bills, SUM(total) as amount')
            ->groupBy('branch_id')
            ->get();

        $branches = Branch::query()->whereIn('id', $byBranch->pluck('branch_id'))->get()->keyBy('id');

        return view('admin.dashboard', [
            'saleCount' => (clone $today)->count(),
            'saleTotal' => (clone $today)->sum('total'),
            'salesByBranch' => $byBranch->map(fn ($row) => [
                'branch' => $branches->get($row->branch_id)?->name ?? '—',
                'bills' => (int) $row->bills,
                'amount' => (float) $row->amount,
            ]),
            'recentSales' => Order::query()
                ->with(['branch', 'customer'])
                ->where('channel', Order::CHANNEL_BRANCH)
                ->orderByDesc('id')
                ->limit(8)
                ->get(),
            'waitingTransfers' => StockTransfer::query()
                ->with('branch')
                ->where('status', StockTransfer::STATUS_DISPATCHED)
                ->orderBy('dispatched_at')
                ->limit(8)
                ->get(),
            'waitingCount' => StockTransfer::query()->where('status', StockTransfer::STATUS_DISPATCHED)->count(),
            'draftCount' => StockTransfer::query()->where('status', StockTransfer::STATUS_DRAFT)->count(),
            'openRequests' => StockRequest::query()
                ->with('branch')
                ->where('status', StockRequest::STATUS_REQUESTED)
                ->whereNull('stock_transfer_id')
                ->orderBy('id')
                ->limit(8)
                ->get(),
            'openRequestCount' => StockRequest::query()
                ->where('status', StockRequest::STATUS_REQUESTED)
                ->whereNull('stock_transfer_id')
                ->count(),
        ]);
    }
}
