<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WarehouseStockRequest;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Support\StockLedger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WarehouseStockController extends Controller
{
    public function index(): View
    {
        $stocks = Stock::query()
            ->whereNotNull('warehouse_id')
            ->with(['product.category', 'warehouse'])
            ->orderBy('warehouse_id')
            ->orderBy('product_id')
            ->paginate(15);

        return view('admin.stocks.index', [
            'stocks' => $stocks,
        ]);
    }

    public function create(): View
    {
        return view('admin.stocks.create', [
            'stock' => new Stock(['quantity' => 0]),
            'products' => $this->products(),
            'warehouses' => $this->warehouses(),
        ]);
    }

    public function store(WarehouseStockRequest $request, StockLedger $ledger): RedirectResponse
    {
        $product = Product::query()->findOrFail($request->integer('product_id'));
        $warehouse = Warehouse::query()->findOrFail($request->integer('warehouse_id'));

        $ledger->openWarehouseStock(
            $product,
            $warehouse,
            $request->integer('quantity'),
            $request->filled('note') ? $request->string('note')->toString() : null,
            $request->user()?->id,
        );

        return redirect()->route('admin.stocks.index')->with('success', 'Stock saved.');
    }

    public function edit(Stock $stock): View
    {
        abort_unless($stock->warehouse_id, 404);

        $stock->load(['product', 'warehouse', 'movements.user']);

        return view('admin.stocks.edit', [
            'stock' => $stock,
            'movements' => $stock->movements->sortByDesc('id')->take(10),
        ]);
    }

    public function update(WarehouseStockRequest $request, Stock $stock, StockLedger $ledger): RedirectResponse
    {
        abort_unless($stock->warehouse_id, 404);

        $before = $stock->quantity;
        $ledger->setQuantity(
            $stock,
            $request->integer('quantity'),
            $request->filled('note') ? $request->string('note')->toString() : null,
            $request->user()?->id,
        );

        $message = $stock->quantity === $before ? 'Quantity is unchanged.' : 'Stock updated.';

        return redirect()->route('admin.stocks.index')->with('success', $message);
    }

    private function products()
    {
        return Product::query()
            ->where('status', Product::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();
    }

    private function warehouses()
    {
        return Warehouse::query()
            ->where('status', Warehouse::STATUS_ACTIVE)
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->get();
    }
}
