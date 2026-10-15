<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Http\Requests\Branch\CompleteTransferRequest;
use App\Models\Agent;
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
        $agent = $this->agent($request);

        $transfers = StockTransfer::query()
            ->with('warehouse')
            ->where('agent_id', $agent->id)
            ->whereIn('status', [
                StockTransfer::STATUS_DISPATCHED,
                StockTransfer::STATUS_RECEIVED,
                StockTransfer::STATUS_RETURNED,
                StockTransfer::STATUS_PARTIAL,
            ])
            ->orderByDesc('id')
            ->paginate(15);

        return view('agent.transfers.index', [
            'transfers' => $transfers,
        ]);
    }

    public function show(Request $request, StockTransfer $transfer): View
    {
        $agent = $this->agent($request);
        $this->ensureShopTransfer($transfer, $agent);
        $transfer->load(['items.product', 'warehouse', 'agent']);

        return view('agent.transfers.show', [
            'transfer' => $transfer,
        ]);
    }

    public function complete(Request $request, StockTransfer $transfer, StockTransferWorkflow $workflow): RedirectResponse
    {
        $agent = $this->agent($request);
        $this->ensureShopTransfer($transfer, $agent);

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
            StockTransfer::STATUS_PARTIAL => $transfer->code.' completed. Accepted stock is at the shop and the rest is back at the warehouse.',
            default => $transfer->code.' received.',
        };

        return redirect()
            ->route('agent.transfers.show', $transfer)
            ->with('success', $message);
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

    private function ensureShopTransfer(StockTransfer $transfer, Agent $agent): void
    {
        abort_unless($transfer->agent_id === $agent->id, 404);
        abort_unless($transfer->isDispatched() || $transfer->isSettled(), 404);
    }
}
