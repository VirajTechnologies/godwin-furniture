<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Http\Requests\Branch\StockRequestRequest;
use App\Models\Branch;
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
        $branch = $this->managerBranch($request);

        $requests = StockRequest::query()
            ->with('transfer')
            ->where('branch_id', $branch->id)
            ->orderByDesc('id')
            ->paginate(15);

        return view('branch.stock-requests.index', [
            'requests' => $requests,
        ]);
    }

    public function create(Request $request): View
    {
        $this->managerBranch($request);

        return view('branch.stock-requests.create', [
            'products' => $this->products(),
        ]);
    }

    public function store(StockRequestRequest $request): RedirectResponse
    {
        $branch = $this->managerBranch($request);

        $stockRequest = DB::transaction(function () use ($request, $branch) {
            $stockRequest = StockRequest::query()->create([
                'code' => 'Q'.substr((string) Str::ulid(), 0, 19),
                'branch_id' => $branch->id,
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
            ->route('branch.stock-requests.show', $stockRequest)
            ->with('success', 'Stock request '.$stockRequest->code.' sent.');
    }

    public function show(Request $request, StockRequest $stockRequest): View
    {
        $branch = $this->managerBranch($request);
        abort_unless($stockRequest->branch_id === $branch->id, 404);
        $stockRequest->load(['items.product', 'transfer']);

        return view('branch.stock-requests.show', [
            'stockRequest' => $stockRequest,
        ]);
    }

    public function cancel(Request $request, StockRequest $stockRequest): RedirectResponse
    {
        $branch = $this->managerBranch($request);
        abort_unless($stockRequest->branch_id === $branch->id, 404);

        if (! $stockRequest->isRequested() || $stockRequest->stock_transfer_id) {
            return back()->with('error', 'Only an open request can be cancelled.');
        }

        $stockRequest->update([
            'status' => StockRequest::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        return redirect()
            ->route('branch.stock-requests.index')
            ->with('success', $stockRequest->code.' cancelled.');
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

    private function products()
    {
        return Product::query()
            ->where('status', Product::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();
    }
}
