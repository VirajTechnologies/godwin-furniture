<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AgentRequest;
use App\Models\Agent;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use App\Support\LocationChoices;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AgentController extends Controller
{
    public function index(): View
    {
        $agents = Agent::query()
            ->with(['user', 'city'])
            ->orderBy('name')
            ->paginate(15);

        return view('admin.agents.index', [
            'agents' => $agents,
        ]);
    }

    public function create(): View
    {
        $agent = new Agent(['status' => Agent::STATUS_ACTIVE]);

        return view('admin.agents.create', [
            'agent' => $agent,
            'warehouses' => $this->warehouses(),
            ...$this->locationChoices($agent),
        ]);
    }

    public function store(AgentRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $role = Role::query()->where('slug', Role::AGENT)->firstOrFail();
            $user = User::query()->create([
                'name' => $request->validated('contact_person') ?: $request->validated('name'),
                'email' => $request->validated('email'),
                'phone' => $request->validated('phone'),
                'password' => $request->validated('password'),
                'role_id' => $role->id,
                'status' => $request->validated('status'),
            ]);

            Agent::query()->create([
                ...$request->safe()->except(['email', 'password']),
                'user_id' => $user->id,
            ]);
        });

        return redirect()
            ->route('admin.agents.index')
            ->with('success', 'Agent saved.');
    }

    public function edit(Agent $agent): View
    {
        $agent->load('user');

        return view('admin.agents.edit', [
            'agent' => $agent,
            'warehouses' => $this->warehouses((int) old('warehouse_id', $agent->warehouse_id) ?: null),
            ...$this->locationChoices($agent),
        ]);
    }

    public function update(AgentRequest $request, Agent $agent): RedirectResponse
    {
        DB::transaction(function () use ($request, $agent): void {
            $agent->update($request->safe()->except(['code', 'email', 'password']));

            $user = [
                'name' => $request->validated('contact_person') ?: $request->validated('name'),
                'email' => $request->validated('email'),
                'phone' => $request->validated('phone'),
                'status' => $request->validated('status'),
            ];

            if ($request->filled('password')) {
                $user['password'] = $request->validated('password');
            }

            $agent->user()->update($user);
        });

        return redirect()
            ->route('admin.agents.index')
            ->with('success', 'Agent updated.');
    }

    private function warehouses(?int $selectedId = null)
    {
        return Warehouse::query()
            ->where(function ($query) use ($selectedId): void {
                $query->where('status', Warehouse::STATUS_ACTIVE);

                if ($selectedId) {
                    $query->orWhere('id', $selectedId);
                }
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function locationChoices(Agent $agent): array
    {
        $stateId = (int) old('state_id', $agent->state_id) ?: null;
        $districtId = (int) old('district_id', $agent->district_id) ?: null;
        $cityId = (int) old('city_id', $agent->city_id) ?: null;

        return [
            'states' => LocationChoices::states($stateId),
            'districts' => LocationChoices::districts($stateId, $districtId),
            'cities' => LocationChoices::cities($districtId, $cityId),
        ];
    }
}
