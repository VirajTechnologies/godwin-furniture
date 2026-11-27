<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomRequestController extends Controller
{
    public function index(): View
    {
        $requests = CustomRequest::query()
            ->with(['agent', 'branch', 'product'])
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.custom-requests.index', [
            'requests' => $requests,
        ]);
    }

    public function show(CustomRequest $customRequest): View
    {
        $customRequest->load(['agent', 'branch', 'product', 'requester', 'handler']);

        return view('admin.custom-requests.show', [
            'customRequest' => $customRequest,
            'statusUrl' => route('admin.custom-requests.status', $customRequest),
        ]);
    }

    public function updateStatus(Request $request, CustomRequest $customRequest): RedirectResponse
    {
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
}
