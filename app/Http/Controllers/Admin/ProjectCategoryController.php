<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\FiltersCatalogMasters;
use App\Http\Controllers\Admin\Concerns\UpdatesActiveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProjectCategoryRequest;
use App\Models\BusinessGroup;
use App\Models\ProjectCategory;
use App\Support\SyncStoreCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectCategoryController extends Controller
{
    use FiltersCatalogMasters, UpdatesActiveStatus;

    public function index(Request $request): View
    {
        $filters = $this->catalogFilters($request, ['business_group_id', 'show_on_store']);
        $businessGroups = BusinessGroup::query()->orderBy('name')->get();

        if ($filters['business_group_id'] !== '' && ! $businessGroups->contains('id', (int) $filters['business_group_id'])) {
            $filters['business_group_id'] = '';
        }

        $query = ProjectCategory::query()->with(['businessGroup']);
        $this->applyCatalogFilters($query, $filters);
        $query->when(
            $filters['business_group_id'] !== '',
            fn ($builder) => $builder->where('business_group_id', (int) $filters['business_group_id'])
        );

        return view('admin.project-categories.index', [
            'filters' => $filters,
            'filterBusinessGroups' => $businessGroups,
            'projectCategories' => $query->orderBy('sort_order')->orderBy('name')->paginate(20)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.project-categories.create', [
            'projectCategory' => new ProjectCategory(['status' => ProjectCategory::STATUS_ACTIVE, 'show_on_store' => true, 'sort_order' => 0]),
            'businessGroups' => BusinessGroup::query()->orderBy('name')->get(),
        ]);
    }

    public function store(ProjectCategoryRequest $request): RedirectResponse
    {
        $record = ProjectCategory::query()->create($request->validated());
        SyncStoreCategory::syncProject($record);

        return redirect()->route('admin.project-categories.index')->with('success', 'Project saved.');
    }

    public function edit(ProjectCategory $projectCategory): View
    {
        return view('admin.project-categories.edit', [
            'projectCategory' => $projectCategory,
            'businessGroups' => BusinessGroup::query()->orderBy('name')->get(),
        ]);
    }

    public function update(ProjectCategoryRequest $request, ProjectCategory $projectCategory): RedirectResponse
    {
        $projectCategory->update($request->validated());
        $this->resyncProjectTree($projectCategory->fresh());

        return redirect()->route('admin.project-categories.index')->with('success', 'Project updated.');
    }

    public function updateStatus(Request $request, ProjectCategory $projectCategory): RedirectResponse
    {
        $response = $this->updateActiveStatus($request, $projectCategory, $projectCategory->name);
        $this->resyncProjectTree($projectCategory->fresh());

        return $response;
    }

    private function resyncProjectTree(ProjectCategory $project): void
    {
        SyncStoreCategory::syncProject($project);

        $project->rooms()->each(function ($room): void {
            SyncStoreCategory::syncRoom($room);
            $room->productCategories()->each(
                fn ($leaf) => SyncStoreCategory::syncProductCategory($leaf)
            );
        });
    }
}
