<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\FiltersCatalogMasters;
use App\Http\Controllers\Admin\Concerns\UpdatesActiveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CatalogRoomRequest;
use App\Models\CatalogRoom;
use App\Models\ProjectCategory;
use App\Support\SyncStoreCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogRoomController extends Controller
{
    use FiltersCatalogMasters, UpdatesActiveStatus;

    public function index(Request $request): View
    {
        $filters = $this->catalogFilters($request, ['project_category_id', 'show_on_store']);
        $projects = ProjectCategory::query()->orderBy('name')->get();

        if ($filters['project_category_id'] !== '' && ! $projects->contains('id', (int) $filters['project_category_id'])) {
            $filters['project_category_id'] = '';
        }

        $query = CatalogRoom::query()->with(['projectCategory']);
        $this->applyCatalogFilters($query, $filters);
        $query->when(
            $filters['project_category_id'] !== '',
            fn ($builder) => $builder->where('project_category_id', (int) $filters['project_category_id'])
        );

        return view('admin.catalog-rooms.index', [
            'filters' => $filters,
            'filterProjects' => $projects,
            'catalogRooms' => $query->orderBy('sort_order')->orderBy('name')->paginate(20)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.catalog-rooms.create', [
            'catalogRoom' => new CatalogRoom(['status' => CatalogRoom::STATUS_ACTIVE, 'show_on_store' => true, 'sort_order' => 0]),
            'projectCategories' => ProjectCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function store(CatalogRoomRequest $request): RedirectResponse
    {
        $record = CatalogRoom::query()->create($request->validated());
        SyncStoreCategory::syncRoom($record);

        return redirect()->route('admin.catalog-rooms.index')->with('success', 'Room saved.');
    }

    public function edit(CatalogRoom $catalogRoom): View
    {
        return view('admin.catalog-rooms.edit', [
            'catalogRoom' => $catalogRoom,
            'projectCategories' => ProjectCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function update(CatalogRoomRequest $request, CatalogRoom $catalogRoom): RedirectResponse
    {
        $catalogRoom->update($request->validated());
        SyncStoreCategory::syncRoom($catalogRoom->fresh());

        return redirect()->route('admin.catalog-rooms.index')->with('success', 'Room updated.');
    }

    public function updateStatus(Request $request, CatalogRoom $catalogRoom): RedirectResponse
    {
        $response = $this->updateActiveStatus($request, $catalogRoom, $catalogRoom->name);
        SyncStoreCategory::syncRoom($catalogRoom->fresh());

        return $response;
    }
}
