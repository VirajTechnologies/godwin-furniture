<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agent\StockRequestRequest;
use App\Models\Agent;
use App\Models\Product;
use App\Models\StockRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StockRequestController extends Controller
{
    public function index(Request $request): View
    {
        $agent = $this->agent($request);

        $requests = StockRequest::query()
            ->with('transfer')
            ->where('agent_id', $agent->id)
            ->orderByDesc('id')
            ->paginate(15);

        return view('agent.stock-requests.index', [
            'requests' => $requests,
        ]);
    }

    public function create(Request $request): View
    {
        $this->agent($request);

        return view('agent.stock-requests.create', [
            'products' => $this->products(),
        ]);
    }

    public function store(StockRequestRequest $request): RedirectResponse
    {
        $agent = $this->agent($request);

        $stockRequest = DB::transaction(function () use ($request, $agent) {
            $stockRequest = StockRequest::query()->create([
                'code' => 'Q'.substr((string) Str::ulid(), 0, 19),
                'agent_id' => $agent->id,
                'status' => StockRequest::STATUS_REQUESTED,
                'notes' => $request->validated('notes'),
                'requested_by' => $request->user()?->id,
            ]);

            $stockRequest->update([
                'code' => 'R'.str_pad((string) $stockRequest->id, 5, '0', STR_PAD_LEFT),
            ]);
            $stockRequest->items()->createMany($request->lines());

            return $stockRequest;
        });

        return redirect()
            ->route('agent.stock-requests.show', $stockRequest)
            ->with('success', 'Stock request '.$stockRequest->code.' sent.');
    }

    public function show(Request $request, StockRequest $stockRequest): View
    {
        $agent = $this->agent($request);
        abort_unless($stockRequest->agent_id === $agent->id, 404);
        $stockRequest->load(['items.product', 'transfer']);

        return view('agent.stock-requests.show', [
            'stockRequest' => $stockRequest,
        ]);
    }

    public function cancel(Request $request, StockRequest $stockRequest): RedirectResponse
    {
        $agent = $this->agent($request);
        abort_unless($stockRequest->agent_id === $agent->id, 404);

        if (! $stockRequest->isRequested() || $stockRequest->stock_transfer_id) {
            return back()->with('error', 'Only an open request can be cancelled.');
        }

        $stockRequest->update([
            'status' => StockRequest::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        return redirect()
            ->route('agent.stock-requests.index')
            ->with('success', $stockRequest->code.' cancelled.');
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

    private function products()
    {
        return Product::query()
            ->where('status', Product::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();
    }
}
