<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockRequest;
use App\Models\StockTransfer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StockRequestController extends Controller
{
    public function index(): View
    {
        $requests = StockRequest::query()
            ->with(['branch', 'transfer'])
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.stock-requests.index', [
            'requests' => $requests,
        ]);
    }

    public function show(StockRequest $stockRequest): View
    {
        $stockRequest->load(['branch.warehouse', 'items.product', 'transfer', 'requester']);

        return view('admin.stock-requests.show', [
            'stockRequest' => $stockRequest,
        ]);
    }

    public function createTransfer(Request $request, StockRequest $stockRequest): RedirectResponse
    {
        $stockRequest->load(['branch.warehouse', 'items', 'transfer']);

        if (! $stockRequest->isRequested()) {
            return back()->with('error', 'Only an open request can become a transfer.');
        }

        if ($stockRequest->transfer && ! $stockRequest->transfer->isCancelled()) {
            return redirect()
                ->route('admin.transfers.edit', $stockRequest->transfer)
                ->with('success', 'This request already has transfer '.$stockRequest->transfer->code.'.');
        }

        $branch = $stockRequest->branch;
        $warehouse = $branch?->warehouse;

        if (! $branch || ! $branch->isActive() || ! $warehouse || ! $warehouse->isActive()) {
            return back()->with('error', 'The branch or its supplying warehouse is inactive.');
        }

        if ($stockRequest->items->isEmpty()) {
            return back()->with('error', 'This request has no lines.');
        }

        $transfer = DB::transaction(function () use ($request, $stockRequest, $branch) {
            $transfer = StockTransfer::query()->create([
                'code' => 'T'.substr((string) Str::ulid(), 0, 19),
                'warehouse_id' => $branch->warehouse_id,
                'branch_id' => $branch->id,
                'status' => StockTransfer::STATUS_DRAFT,
                'notes' => $stockRequest->notes,
                'created_by' => $request->user()?->id,
            ]);

            $transfer->update([
                'code' => 'TR'.str_pad((string) $transfer->id, 5, '0', STR_PAD_LEFT),
            ]);
            $transfer->items()->createMany($stockRequest->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
            ])->all());

            $stockRequest->update([
                'stock_transfer_id' => $transfer->id,
            ]);

            return $transfer;
        });

        return redirect()
            ->route('admin.transfers.edit', $transfer)
            ->with('success', 'Transfer '.$transfer->code.' created from '.$stockRequest->code.'. Dispatch it when the warehouse sends the pieces.');
    }
}
