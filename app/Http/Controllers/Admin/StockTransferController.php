<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InsufficientStock;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StockTransferRequest;
use App\Models\Branch;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Support\StockTransferWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class StockTransferController extends Controller
{
    public function index(): View
    {
        $transfers = StockTransfer::query()
            ->with(['warehouse', 'branch'])
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.transfers.index', [
            'transfers' => $transfers,
        ]);
    }

    public function create(): View
    {
        $transfer = new StockTransfer([
            'status' => StockTransfer::STATUS_DRAFT,
        ]);

        return view('admin.transfers.create', [
            'transfer' => $transfer,
            'branches' => $this->branches($transfer),
            'products' => $this->products($transfer),
        ]);
    }

    public function store(StockTransferRequest $request): RedirectResponse
    {
        $branch = Branch::query()->findOrFail($request->integer('branch_id'));

        $transfer = DB::transaction(function () use ($request, $branch) {
            $transfer = StockTransfer::query()->create([
                'code' => $request->validated('code'),
                'warehouse_id' => $branch->warehouse_id,
                'branch_id' => $branch->id,
                'status' => StockTransfer::STATUS_DRAFT,
                'notes' => $request->validated('notes'),
                'created_by' => $request->user()?->id,
            ]);

            $transfer->items()->createMany($request->lines());

            return $transfer;
        });

        return redirect()
            ->route('admin.transfers.edit', $transfer)
            ->with('success', 'Transfer saved.');
    }

    public function edit(StockTransfer $transfer): View
    {
        $transfer->load(['items.product', 'warehouse', 'branch']);

        return view('admin.transfers.edit', [
            'transfer' => $transfer,
            'branches' => $this->branches($transfer),
            'products' => $this->products($transfer),
        ]);
    }

    public function update(StockTransferRequest $request, StockTransfer $transfer): RedirectResponse
    {
        if (! $transfer->isDraft()) {
            return back()->with('error', 'Only a draft transfer can be changed.');
        }

        $branch = Branch::query()->findOrFail($request->integer('branch_id'));

        DB::transaction(function () use ($request, $transfer, $branch) {
            $transfer->update([
                'warehouse_id' => $branch->warehouse_id,
                'branch_id' => $branch->id,
                'notes' => $request->validated('notes'),
            ]);

            $transfer->items()->delete();
            $transfer->items()->createMany($request->lines());
        });

        return redirect()
            ->route('admin.transfers.edit', $transfer)
            ->with('success', 'Transfer updated.');
    }

    public function dispatch(Request $request, StockTransfer $transfer, StockTransferWorkflow $workflow): RedirectResponse
    {
        if (! $transfer->isDraft()) {
            return back()->with('error', 'Only a draft transfer can be dispatched.');
        }

        try {
            $workflow->dispatch($transfer, (int) $request->user()->id);
        } catch (InsufficientStock $exception) {
            return back()->with('error', $exception->getMessage());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.transfers.edit', $transfer)
            ->with('success', $transfer->code.' dispatched.');
    }

    public function cancel(StockTransfer $transfer): RedirectResponse
    {
        if (! $transfer->isDraft()) {
            return back()->with('error', 'Only a draft transfer can be cancelled.');
        }

        $transfer->update([
            'status' => StockTransfer::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        return redirect()
            ->route('admin.transfers.index')
            ->with('success', $transfer->code.' cancelled.');
    }

    private function branches(StockTransfer $transfer)
    {
        $selectedId = (int) old('branch_id', $transfer->branch_id) ?: null;

        return Branch::query()
            ->with('warehouse')
            ->where(function ($query) use ($selectedId) {
                $query->where('status', Branch::STATUS_ACTIVE);

                if ($selectedId) {
                    $query->orWhere('id', $selectedId);
                }
            })
            ->orderBy('name')
            ->get();
    }

    private function products(StockTransfer $transfer)
    {
        $selected = collect(old('items', []))
            ->pluck('product_id')
            ->filter()
            ->map(fn ($id) => (int) $id);

        if ($transfer->exists) {
            $selected = $selected->merge($transfer->items()->pluck('product_id'));
        }

        return Product::query()
            ->where(function ($query) use ($selected) {
                $query->where('status', Product::STATUS_ACTIVE);

                if ($selected->isNotEmpty()) {
                    $query->orWhereIn('id', $selected->all());
                }
            })
            ->orderBy('name')
            ->get();
    }
}
