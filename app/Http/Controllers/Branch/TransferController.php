<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\StockTransfer;
use App\Support\StockTransferWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class TransferController extends Controller
{
    public function index(Request $request): View
    {
        $branch = $this->managerBranch($request);

        $transfers = StockTransfer::query()
            ->with('warehouse')
            ->where('branch_id', $branch->id)
            ->whereIn('status', [StockTransfer::STATUS_DISPATCHED, StockTransfer::STATUS_RECEIVED])
            ->orderByDesc('id')
            ->paginate(15);

        return view('branch.transfers.index', [
            'transfers' => $transfers,
        ]);
    }

    public function show(Request $request, StockTransfer $transfer): View
    {
        $branch = $this->managerBranch($request);
        $this->ensureShowroomTransfer($transfer, $branch);
        $transfer->load(['items.product', 'warehouse', 'branch']);

        return view('branch.transfers.show', [
            'transfer' => $transfer,
        ]);
    }

    public function receive(Request $request, StockTransfer $transfer, StockTransferWorkflow $workflow): RedirectResponse
    {
        $branch = $this->managerBranch($request);
        $this->ensureShowroomTransfer($transfer, $branch);

        if (! $transfer->isDispatched()) {
            return back()->with('error', 'Only a dispatched transfer can be received.');
        }

        try {
            $workflow->receive($transfer, (int) $request->user()->id);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('branch.transfers.show', $transfer)
            ->with('success', $transfer->code.' received.');
    }

    private function managerBranch(Request $request): Branch
    {
        $user = $request->user();
        $user?->loadMissing('role', 'employee.branch');
        $branch = $user?->employee?->branch;

        abort_unless(
            $user?->isBranchManager()
            && $user->status === 'active'
            && $user->employee?->isActive()
            && $branch instanceof Branch
            && $branch->isActive(),
            403,
        );

        return $branch;
    }

    private function ensureShowroomTransfer(StockTransfer $transfer, Branch $branch): void
    {
        abort_unless($transfer->branch_id === $branch->id, 404);
        abort_unless($transfer->isDispatched() || $transfer->isReceived(), 404);
    }
}