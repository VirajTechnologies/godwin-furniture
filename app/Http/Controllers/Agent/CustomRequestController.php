<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agent\CustomRequestRequest;
use App\Models\Agent;
use App\Models\Branch;
use App\Models\CustomRequest;
use App\Models\Product;
use App\Support\Catalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomRequestController extends Controller
{
    public function index(Request $request): View
    {
        $agent = $this->agent($request);

        $requests = CustomRequest::query()
            ->with(['product', 'branch'])
            ->where('agent_id', $agent->id)
            ->orderByDesc('id')
            ->paginate(15);

        return view('agent.custom-requests.index', [
            'requests' => $requests,
        ]);
    }

    public function create(Request $request): View
    {
        $this->agent($request);

        return view('agent.custom-requests.create', [
            'primaryBranch' => Branch::primaryForCustomRequests(),
            'catalog' => Catalog::clientConfig(),
            'state' => [
                'project' => (string) old('project_category', ''),
                'room' => (string) old('room', ''),
                'category' => (string) old('product_category', ''),
                'type' => (string) old('design_type', ''),
                'model' => (string) old('model_name', ''),
                'specs' => (object) old('specs', []),
                'variants' => old('variants', []),
                'modelNotListed' => old('model_not_listed', false),
            ],
        ]);
    }

    public function store(CustomRequestRequest $request): RedirectResponse
    {
        $agent = $this->agent($request);
        $branch = Branch::primaryForCustomRequests();

        if (! $branch) {
            return back()
                ->withInput()
                ->with('error', 'No primary branch is set. Ask head office to mark a branch as primary.');
        }

        $payload = $request->payload();

        $productId = null;
        if ($payload['match_mode'] === CustomRequest::MODE_MATCHED) {
            $productId = Product::query()
                ->where('name', $payload['model_name'])
                ->where('project_category', $payload['project_category'])
                ->where('room', $payload['room'])
                ->where('product_category', $payload['product_category'])
                ->value('id');
        }

        $customRequest = DB::transaction(function () use ($request, $agent, $branch, $payload, $productId) {
            $customRequest = CustomRequest::query()->create(array_merge($payload, [
                'code' => 'T'.substr((string) Str::ulid(), 0, 19),
                'agent_id' => $agent->id,
                'branch_id' => $branch->id,
                'product_id' => $productId,
                'status' => CustomRequest::STATUS_REQUESTED,
                'requested_by' => $request->user()?->id,
            ]));

            $customRequest->update([
                'code' => 'CR'.str_pad((string) $customRequest->id, 5, '0', STR_PAD_LEFT),
            ]);

            return $customRequest;
        });

        return redirect()
            ->route('agent.custom-requests.show', $customRequest)
            ->with('success', 'Custom request '.$customRequest->code.' sent to '.$branch->name.'.');
    }

    public function show(Request $request, CustomRequest $customRequest): View
    {
        $agent = $this->agent($request);
        abort_unless($customRequest->agent_id === $agent->id, 404);
        $customRequest->load(['product', 'branch', 'handler']);

        return view('agent.custom-requests.show', [
            'customRequest' => $customRequest,
        ]);
    }

    public function cancel(Request $request, CustomRequest $customRequest): RedirectResponse
    {
        $agent = $this->agent($request);
        abort_unless($customRequest->agent_id === $agent->id, 404);

        if (! $customRequest->isRequested()) {
            return back()->with('error', 'Only an open request can be cancelled.');
        }

        $customRequest->update([
            'status' => CustomRequest::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        return redirect()
            ->route('agent.custom-requests.index')
            ->with('success', $customRequest->code.' cancelled.');
    }

    private function agent(Request $request): Agent
    {
        $user = $request->user();
        abort_unless($user?->isAgentManager(), 403);

        $agent = $user->agent;
        abort_unless($agent && $user->isActive() && $agent->isActive(), 403);

        return $agent;
    }
}
