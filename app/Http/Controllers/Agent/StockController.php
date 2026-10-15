<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $agent = $request->user()->agent;

        $stocks = Stock::query()
            ->with('product.category.parent')
            ->where('agent_id', $agent->id)
            ->whereNull('warehouse_id')
            ->orderBy('product_id')
            ->paginate(15);

        return view('agent.stock.index', [
            'stocks' => $stocks,
        ]);
    }
}
