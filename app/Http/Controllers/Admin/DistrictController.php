<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\UpdatesActiveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DistrictRequest;
use App\Models\District;
use App\Support\LocationChoices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DistrictController extends Controller
{
    use UpdatesActiveStatus;

    public function index(): View
    {
        $districts = District::query()
            ->with('state')
            ->orderBy('district_name')
            ->paginate(15);

        return view('admin.districts.index', [
            'districts' => $districts,
        ]);
    }

    public function create(): View
    {
        return view('admin.districts.create', [
            'district' => new District(['status' => District::STATUS_ACTIVE]),
            'states' => LocationChoices::states((int) old('state_id') ?: null),
        ]);
    }

    public function store(DistrictRequest $request): RedirectResponse
    {
        District::query()->create($request->validated());

        return redirect()->route('admin.districts.index')->with('success', 'District saved.');
    }

    public function edit(District $district): View
    {
        $stateId = (int) old('state_id', $district->state_id);

        return view('admin.districts.edit', [
            'district' => $district,
            'states' => LocationChoices::states($stateId),
        ]);
    }

    public function update(DistrictRequest $request, District $district): RedirectResponse
    {
        $district->update($request->validated());

        return redirect()->route('admin.districts.index')->with('success', 'District updated.');
    }

    public function updateStatus(Request $request, District $district): RedirectResponse
    {
        return $this->updateActiveStatus($request, $district, $district->district_name);
    }

    public function options(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'state_id' => ['required', 'integer', 'exists:states,id'],
            'selected' => ['nullable', 'integer'],
        ]);

        $districts = LocationChoices::districts(
            (int) $validated['state_id'],
            isset($validated['selected']) ? (int) $validated['selected'] : null,
        );

        return response()->json($districts->map(fn (District $district) => [
            'id' => $district->id,
            'district_name' => $district->district_name,
        ])->values());
    }
}
