<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Stock;
use App\Models\StockRequest;
use App\Models\StockTransfer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $branch = $this->branch($request);
        $manager = $request->user()?->isBranchManager() === true;

        $sales = Order::query()
            ->where('branch_id', $branch->id)
            ->where('channel', Order::CHANNEL_BRANCH)
            ->where('status', Order::STATUS_COMPLETED)
            ->whereDate('created_at', today());

        $stocks = Stock::query()
            ->with('product')
            ->where('branch_id', $branch->id)
            ->orderBy('product_id')
            ->get();

        return view('branch.home', [
            'branch' => $branch,
            'manager' => $manager,
            'saleCount' => (clone $sales)->count(),
            'saleTotal' => (clone $sales)->sum('total'),
            'recentSales' => Order::query()
                ->with('customer')
                ->where('branch_id', $branch->id)
                ->where('channel', Order::CHANNEL_BRANCH)
                ->orderByDesc('id')
                ->limit(8)
                ->get(),
            'stocks' => $stocks,
            'outOfStockCount' => $stocks->where('quantity', 0)->count(),
            'waitingTransfers' => $manager
                ? StockTransfer::query()
                    ->where('branch_id', $branch->id)
                    ->where('status', StockTransfer::STATUS_DISPATCHED)
                    ->orderBy('dispatched_at')
                    ->limit(8)
                    ->get()
                : collect(),
            'waitingCount' => $manager
                ? StockTransfer::query()
                    ->where('branch_id', $branch->id)
                    ->where('status', StockTransfer::STATUS_DISPATCHED)
                    ->count()
                : 0,
            'requests' => $manager
                ? StockRequest::query()
                    ->with('transfer')
                    ->where('branch_id', $branch->id)
                    ->where('status', StockRequest::STATUS_REQUESTED)
                    ->where(function ($query): void {
                        $query->whereNull('stock_transfer_id')
                            ->orWhereHas('transfer', function ($transfer): void {
                                $transfer->whereIn('status', [
                                    StockTransfer::STATUS_DRAFT,
                                    StockTransfer::STATUS_DISPATCHED,
                                ]);
                            });
                    })
                    ->orderBy('id')
                    ->limit(8)
                    ->get()
                : collect(),
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
}
