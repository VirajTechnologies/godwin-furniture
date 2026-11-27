<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\FiltersCatalogMasters;
use App\Http\Controllers\Admin\Concerns\UpdatesActiveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BusinessGroupRequest;
use App\Models\BusinessGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessGroupController extends Controller
{
    use FiltersCatalogMasters, UpdatesActiveStatus;

    public function index(Request $request): View
    {
        $filters = $this->catalogFilters($request);

        return view('admin.business-groups.index', [
            'filters' => $filters,
            'businessGroups' => $this->applyCatalogFilters(BusinessGroup::query(), $filters)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.business-groups.create', [
            'businessGroup' => new BusinessGroup(['status' => BusinessGroup::STATUS_ACTIVE, 'sort_order' => 0]),
        ]);
    }

    public function store(BusinessGroupRequest $request): RedirectResponse
    {
        BusinessGroup::query()->create($request->validated());

        return redirect()->route('admin.business-groups.index')->with('success', 'Business Group saved.');
    }

    public function edit(BusinessGroup $businessGroup): View
    {
        return view('admin.business-groups.edit', [
            'businessGroup' => $businessGroup,
        ]);
    }

    public function update(BusinessGroupRequest $request, BusinessGroup $businessGroup): RedirectResponse
    {
        $businessGroup->update($request->validated());

        return redirect()->route('admin.business-groups.index')->with('success', 'Business Group updated.');
    }

    public function updateStatus(Request $request, BusinessGroup $businessGroup): RedirectResponse
    {
        return $this->updateActiveStatus($request, $businessGroup, $businessGroup->name);
    }
}
