<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Agent;
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
        $agent = $this->agent($request);
        [$from, $to] = $this->range($request);

        $rows = OrderItem::query()
            ->selectRaw('product_id, SUM(quantity) as quantity, SUM(line_total) as amount, SUM(commission_amount) as commission')
            ->whereHas('order', function ($query) use ($agent, $from, $to): void {
                $query->where('agent_id', $agent->id)
                    ->where('channel', Order::CHANNEL_AGENT)
                    ->where('status', Order::STATUS_COMPLETED)
                    ->whereDate('created_at', '>=', $from->toDateString())
                    ->whereDate('created_at', '<=', $to->toDateString());
            })
            ->groupBy('product_id')
            ->orderByDesc('amount')
            ->get();

        return view('agent.reports.sales-by-product', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'rows' => $rows,
            'products' => Product::query()->whereIn('id', $rows->pluck('product_id'))->get()->keyBy('id'),
        ]);
    }

    public function stock(Request $request): View
    {
        $agent = $this->agent($request);

        return view('agent.reports.stock', [
            'stocks' => Stock::query()
                ->with('product')
                ->where('agent_id', $agent->id)
                ->whereNull('warehouse_id')
                ->orderBy('product_id')
                ->get(),
        ]);
    }

    public function transfers(Request $request): View
    {
        $agent = $this->managerAgent($request);
        [$from, $to] = $this->range($request);

        $transfers = StockTransfer::query()
            ->with('warehouse')
            ->where('agent_id', $agent->id)
            ->whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString())
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('agent.reports.transfers', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'transfers' => $transfers,
        ]);
    }

    public function stockRequests(Request $request): View
    {
        $agent = $this->managerAgent($request);
        [$from, $to] = $this->range($request);

        $requests = StockRequest::query()
            ->with('transfer')
            ->where('agent_id', $agent->id)
            ->whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString())
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('agent.reports.stock-requests', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'requests' => $requests,
        ]);
    }

    private function agent(Request $request): Agent
    {
        $user = $request->user();
        $user?->loadMissing('role', 'agent');
        $agent = $user?->agent;

        abort_unless(
            $user?->isAgent()
            && $user->isActive()
            && $agent instanceof Agent
            && $agent->isActive(),
            403,
        );

        return $agent;
    }

    private function managerAgent(Request $request): Agent
    {
        $agent = $this->agent($request);

        abort_unless($request->user()?->isAgentManager(), 403);

        return $agent;
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
