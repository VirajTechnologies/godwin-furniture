<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WarehouseRequest;
use App\Models\Warehouse;
use App\Support\LocationChoices;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(): View
    {
        $warehouses = Warehouse::query()
            ->with(['state', 'district', 'city'])
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.warehouses.index', [
            'warehouses' => $warehouses,
        ]);
    }

    public function create(): View
    {
        $warehouse = new Warehouse([
            'status' => Warehouse::STATUS_ACTIVE,
            'is_primary' => Warehouse::query()->doesntExist(),
        ]);

        return view('admin.warehouses.create', [
            'warehouse' => $warehouse,
            ...$this->locationChoices($warehouse),
        ]);
    }

    public function store(WarehouseRequest $request): RedirectResponse
    {
        $makePrimary = $request->boolean('is_primary') || Warehouse::query()->doesntExist();

        $warehouse = Warehouse::query()->create($request->safe()->except('is_primary'));

        if ($makePrimary) {
            $warehouse->markAsPrimary();
        }

        return redirect()
            ->route('admin.warehouses.index')
            ->with('success', 'Warehouse saved.');
    }

    public function edit(Warehouse $warehouse): View
    {
        return view('admin.warehouses.edit', [
            'warehouse' => $warehouse,
            ...$this->locationChoices($warehouse),
        ]);
    }

    public function update(WarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $warehouse->update($request->safe()->except(['is_primary', 'code']));

        if ($request->boolean('is_primary')) {
            $warehouse->markAsPrimary();
        }

        return redirect()
            ->route('admin.warehouses.index')
            ->with('success', 'Warehouse updated.');
    }

    public function makePrimary(Warehouse $warehouse): RedirectResponse
    {
        if (! $warehouse->isActive()) {
            return back()->with('error', 'Activate this warehouse before marking it as primary.');
        }

        $warehouse->markAsPrimary();

        return back()->with('success', $warehouse->name.' is now the primary warehouse.');
    }

    public function updateStatus(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $status = $request->validate([
            'status' => ['required', Rule::in([Warehouse::STATUS_ACTIVE, Warehouse::STATUS_INACTIVE])],
        ])['status'];

        if ($status === Warehouse::STATUS_INACTIVE && $warehouse->is_primary) {
            return back()->with('error', 'Set another warehouse as primary before deactivating this one.');
        }

        $warehouse->update(['status' => $status]);

        $label = $status === Warehouse::STATUS_ACTIVE ? 'activated' : 'deactivated';

        return back()->with('success', $warehouse->name.' '.$label.'.');
    }

    /**
     * @return array<string, mixed>
     */
    private function locationChoices(Warehouse $warehouse): array
    {
        $stateId = (int) old('state_id', $warehouse->state_id) ?: null;
        $districtId = (int) old('district_id', $warehouse->district_id) ?: null;
        $cityId = (int) old('city_id', $warehouse->city_id) ?: null;

        return [
            'states' => LocationChoices::states($stateId),
            'districts' => LocationChoices::districts($stateId, $districtId),
            'cities' => LocationChoices::cities($districtId, $cityId),
        ];
    }
}
