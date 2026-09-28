<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BranchRequest;
use App\Models\Branch;
use App\Models\Warehouse;
use App\Support\LocationChoices;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(): View
    {
        $branches = Branch::query()
            ->with(['warehouse', 'state', 'district', 'city'])
            ->orderBy('name')
            ->paginate(15);

        return view('admin.branches.index', [
            'branches' => $branches,
        ]);
    }

    public function create(): View
    {
        $branch = new Branch(['status' => Branch::STATUS_ACTIVE]);

        return view('admin.branches.create', [
            'branch' => $branch,
            'warehouses' => $this->warehouses(),
            ...$this->locationChoices($branch),
        ]);
    }

    public function store(BranchRequest $request): RedirectResponse
    {
        Branch::query()->create($request->validated());

        return redirect()
            ->route('admin.branches.index')
            ->with('success', 'Branch saved.');
    }

    public function edit(Branch $branch): View
    {
        return view('admin.branches.edit', [
            'branch' => $branch,
            'warehouses' => $this->warehouses((int) old('warehouse_id', $branch->warehouse_id) ?: null),
            ...$this->locationChoices($branch),
        ]);
    }

    public function update(BranchRequest $request, Branch $branch): RedirectResponse
    {
        $branch->update($request->safe()->except('code'));

        return redirect()
            ->route('admin.branches.index')
            ->with('success', 'Branch updated.');
    }

    public function updateStatus(Request $request, Branch $branch): RedirectResponse
    {
        $status = $request->validate([
            'status' => ['required', Rule::in([Branch::STATUS_ACTIVE, Branch::STATUS_INACTIVE])],
        ])['status'];

        $branch->update(['status' => $status]);

        $label = $status === Branch::STATUS_ACTIVE ? 'activated' : 'deactivated';

        return back()->with('success', $branch->name.' '.$label.'.');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Warehouse>
     */
    private function warehouses(?int $selectedId = null)
    {
        return Warehouse::query()
            ->where(function ($query) use ($selectedId): void {
                $query->where('status', Warehouse::STATUS_ACTIVE);

                if ($selectedId) {
                    $query->orWhere('id', $selectedId);
                }
            })
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function locationChoices(Branch $branch): array
    {
        $stateId = (int) old('state_id', $branch->state_id) ?: null;
        $districtId = (int) old('district_id', $branch->district_id) ?: null;
        $cityId = (int) old('city_id', $branch->city_id) ?: null;

        return [
            'states' => LocationChoices::states($stateId),
            'districts' => LocationChoices::districts($stateId, $districtId),
            'cities' => LocationChoices::cities($districtId, $cityId),
        ];
    }
}
