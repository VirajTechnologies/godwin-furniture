<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\UpdatesActiveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CityRequest;
use App\Models\City;
use App\Support\LocationChoices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CityController extends Controller
{
    use UpdatesActiveStatus;

    public function index(): View
    {
        $cities = City::query()
            ->with(['state', 'district'])
            ->orderBy('city_name')
            ->paginate(15);

        return view('admin.cities.index', [
            'cities' => $cities,
        ]);
    }

    public function create(): View
    {
        $stateId = (int) old('state_id') ?: null;
        $districtId = (int) old('district_id') ?: null;

        return view('admin.cities.create', [
            'city' => new City(['status' => City::STATUS_ACTIVE]),
            'states' => LocationChoices::states($stateId),
            'districts' => LocationChoices::districts($stateId, $districtId),
        ]);
    }

    public function store(CityRequest $request): RedirectResponse
    {
        City::query()->create($request->validated());

        return redirect()->route('admin.cities.index')->with('success', 'City saved.');
    }

    public function edit(City $city): View
    {
        $stateId = (int) old('state_id', $city->state_id);
        $districtId = (int) old('district_id', $city->district_id);

        return view('admin.cities.edit', [
            'city' => $city,
            'states' => LocationChoices::states($stateId),
            'districts' => LocationChoices::districts($stateId, $districtId),
        ]);
    }

    public function update(CityRequest $request, City $city): RedirectResponse
    {
        $city->update($request->validated());

        return redirect()->route('admin.cities.index')->with('success', 'City updated.');
    }

    public function updateStatus(Request $request, City $city): RedirectResponse
    {
        return $this->updateActiveStatus($request, $city, $city->city_name);
    }

    public function options(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'district_id' => ['required', 'integer', 'exists:districts,id'],
            'selected' => ['nullable', 'integer'],
        ]);

        $cities = LocationChoices::cities(
            (int) $validated['district_id'],
            isset($validated['selected']) ? (int) $validated['selected'] : null,
        );

        return response()->json($cities->map(fn (City $city) => [
            'id' => $city->id,
            'city_name' => $city->city_name,
        ])->values());
    }
}
