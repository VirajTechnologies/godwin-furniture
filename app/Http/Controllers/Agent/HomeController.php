<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $agent = $request->user()->agent;

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

        return view('agent.home', [
            'agent' => $agent,
            'saleCount' => (clone $sales)->count(),
            'saleTotal' => (clone $sales)->sum('total'),
            'commission' => $commission,
            'recentSales' => Order::query()
                ->with('customer')
                ->where('agent_id', $agent->id)
                ->orderByDesc('id')
                ->limit(8)
                ->get(),
        ]);
    }
}
