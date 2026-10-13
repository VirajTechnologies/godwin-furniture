<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Http\Requests\Branch\CompleteTransferRequest;
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
            ->whereIn('status', [
                StockTransfer::STATUS_DISPATCHED,
                StockTransfer::STATUS_RECEIVED,
                StockTransfer::STATUS_RETURNED,
                StockTransfer::STATUS_PARTIAL,
            ])
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

    public function complete(Request $request, StockTransfer $transfer, StockTransferWorkflow $workflow): RedirectResponse
    {
        $branch = $this->managerBranch($request);
        $this->ensureShowroomTransfer($transfer, $branch);

        if (! $transfer->isDispatched()) {
            return back()->with('error', 'Only a dispatched transfer can be completed.');
        }

        $form = CompleteTransferRequest::createFrom($request);
        $form->setContainer(app())->setRedirector(app('redirect'));
        $form->setRouteResolver(fn () => $request->route());
        $form->validateResolved();

        try {
            $workflow->complete($transfer, $form->lines(), (int) $request->user()->id, $form->reason());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $transfer->refresh();
        $message = match ($transfer->status) {
            StockTransfer::STATUS_RETURNED => $transfer->code.' returned to the warehouse.',
            StockTransfer::STATUS_PARTIAL => $transfer->code.' completed. Accepted stock is on the branch and the rest is back at the warehouse.',
            default => $transfer->code.' received.',
        };

        return redirect()
            ->route('branch.transfers.show', $transfer)
            ->with('success', $message);
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
        abort_unless($transfer->isDispatched() || $transfer->isSettled(), 404);
    }
}