<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\CustomRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomRequestController extends Controller
{
    public function index(Request $request): View
    {
        $branch = $this->primaryBranch($request);

        $requests = CustomRequest::query()
            ->with(['agent', 'product'])
            ->where('branch_id', $branch->id)
            ->orderByDesc('id')
            ->paginate(15);

        return view('branch.custom-requests.index', [
            'requests' => $requests,
        ]);
    }

    public function show(Request $request, CustomRequest $customRequest): View
    {
        $branch = $this->primaryBranch($request);
        abort_unless($customRequest->branch_id === $branch->id, 404);
        $customRequest->load(['agent', 'product', 'requester', 'handler']);

        return view('branch.custom-requests.show', [
            'customRequest' => $customRequest,
            'statusUrl' => route('branch.custom-requests.status', $customRequest),
        ]);
    }

    public function updateStatus(Request $request, CustomRequest $customRequest): RedirectResponse
    {
        $branch = $this->primaryBranch($request);
        abort_unless($customRequest->branch_id === $branch->id, 404);

        $data = $request->validate([
            'status' => ['required', Rule::in($customRequest->allowedNextStatuses())],
            'rejection_reason' => [
                Rule::requiredIf($request->input('status') === CustomRequest::STATUS_REJECTED),
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        try {
            $customRequest->transitionTo(
                $data['status'],
                $request->user(),
                $data['rejection_reason'] ?? null,
            );
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $customRequest->code.' marked as '.$customRequest->fresh()->label().'.');
    }

    private function primaryBranch(Request $request): Branch
    {
        $user = $request->user();
        abort_unless($user?->isBranchManager(), 403);

        $branch = $user->employee?->branch;
        abort_unless($branch && $branch->isActive() && $branch->is_primary, 403);

        return $branch;
    }
}
