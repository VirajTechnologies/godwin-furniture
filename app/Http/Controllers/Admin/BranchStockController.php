<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use Illuminate\View\View;

class BranchStockController extends Controller
{
    public function index(): View
    {
        $stocks = Stock::query()
            ->whereNotNull('branch_id')
            ->with(['product.category.parent', 'branch'])
            ->orderBy('branch_id')
            ->orderBy('product_id')
            ->paginate(15);

        return view('admin.branch-stocks.index', [
            'stocks' => $stocks,
        ]);
    }
}
