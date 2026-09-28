<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $stocks = Stock::query()
            ->with('product.category')
            ->where('branch_id', $request->user()->employee->branch_id)
            ->whereNull('warehouse_id')
            ->orderBy('product_id')
            ->paginate(15);

        return view('branch.stock.index', [
            'stocks' => $stocks,
        ]);
    }
}
