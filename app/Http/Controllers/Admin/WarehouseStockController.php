<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InsufficientStock;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WarehouseStockRequest;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockTransfer;
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
            ->with(['product.category.parent', 'warehouse'])
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
        $movements = $stock->movements->sortByDesc('id')->take(10)->values();
        $transferCodes = $movements
            ->map(fn ($movement) => $this->transferCode($movement->note))
            ->filter()
            ->unique()
            ->values();

        $transfers = StockTransfer::query()
            ->with('branch')
            ->whereIn('code', $transferCodes)
            ->get()
            ->keyBy('code');

        $movements->each(function ($movement) use ($transfers): void {
            $code = $this->transferCode($movement->note);
            $movement->setRelation('transfer', $code ? $transfers->get($code) : null);
        });

        return view('admin.stocks.edit', [
            'stock' => $stock,
            'movements' => $movements,
        ]);
    }

    public function update(WarehouseStockRequest $request, Stock $stock, StockLedger $ledger): RedirectResponse
    {
        abort_unless($stock->warehouse_id, 404);

        $quantity = $request->integer('adjust_quantity');
        $direction = $request->string('direction')->toString();

        try {
            $ledger->adjust(
                $stock,
                $quantity,
                $direction,
                $request->string('note')->toString(),
                $request->user()?->id,
            );
        } catch (InsufficientStock $exception) {
            return back()->withInput()->withErrors(['adjust_quantity' => $exception->getMessage()]);
        }

        $message = $direction === 'remove'
            ? $quantity.' removed from warehouse stock.'
            : $quantity.' added to warehouse stock.';

        return redirect()->route('admin.stocks.index')->with('success', $message);
    }

    private function transferCode(?string $note): ?string
    {
        $note = trim((string) $note);

        if (! str_starts_with($note, 'Transfer ')) {
            return null;
        }

        $code = trim(substr($note, strlen('Transfer ')));

        return $code !== '' ? $code : null;
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
            ->orderBy('name')
            ->get();
    }
}
