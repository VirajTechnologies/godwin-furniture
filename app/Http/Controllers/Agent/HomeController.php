<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Stock;
use App\Models\StockRequest;
use App\Models\StockTransfer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $agent = $request->user()->agent;
        $manager = $request->user()?->isAgentManager() === true;

        $sales = Order::query()
            ->where('agent_id', $agent->id)
            ->where('channel', Order::CHANNEL_AGENT)
            ->whereDate('created_at', today());

        $commission = OrderItem::query()
            ->whereHas('order', function ($query) use ($agent): void {
                $query->where('agent_id', $agent->id)
                    ->where('channel', Order::CHANNEL_AGENT);
            })
            ->sum('commission_amount');

        $stocks = Stock::query()
            ->with('product')
            ->where('agent_id', $agent->id)
            ->whereNull('warehouse_id')
            ->orderBy('product_id')
            ->get();

        return view('agent.home', [
            'agent' => $agent,
            'manager' => $manager,
            'saleCount' => (clone $sales)->count(),
            'saleTotal' => (clone $sales)->sum('total'),
            'commission' => $commission,
            'recentSales' => Order::query()
                ->with('customer')
                ->where('agent_id', $agent->id)
                ->orderByDesc('id')
                ->limit(8)
                ->get(),
            'stocks' => $stocks,
            'outOfStockCount' => $stocks->where('quantity', 0)->count(),
            'waitingTransfers' => $manager
                ? StockTransfer::query()
                    ->where('agent_id', $agent->id)
                    ->where('status', StockTransfer::STATUS_DISPATCHED)
                    ->orderBy('dispatched_at')
                    ->limit(8)
                    ->get()
                : collect(),
            'waitingCount' => $manager
                ? StockTransfer::query()
                    ->where('agent_id', $agent->id)
                    ->where('status', StockTransfer::STATUS_DISPATCHED)
                    ->count()
                : 0,
            'requests' => $manager
                ? StockRequest::query()
                    ->with('transfer')
                    ->where('agent_id', $agent->id)
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
}
