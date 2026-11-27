<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\FiltersCatalogMasters;
use App\Http\Controllers\Admin\Concerns\UpdatesActiveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CatalogProductCategoryRequest;
use App\Models\CatalogProductCategory;
use App\Models\CatalogRoom;
use App\Support\SyncStoreCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogProductCategoryController extends Controller
{
    use FiltersCatalogMasters, UpdatesActiveStatus;

    public function index(Request $request): View
    {
        $filters = $this->catalogFilters($request, ['catalog_room_id']);
        $rooms = CatalogRoom::query()->with('projectCategory')->orderBy('name')->get();

        if ($filters['catalog_room_id'] !== '' && ! $rooms->contains('id', (int) $filters['catalog_room_id'])) {
            $filters['catalog_room_id'] = '';
        }

        $query = CatalogProductCategory::query()->with(['room.projectCategory']);
        $this->applyCatalogFilters($query, $filters);
        $query->when(
            $filters['catalog_room_id'] !== '',
            fn ($builder) => $builder->where('catalog_room_id', (int) $filters['catalog_room_id'])
        );

        return view('admin.catalog-product-categories.index', [
            'filters' => $filters,
            'filterRooms' => $rooms,
            'catalogProductCategories' => $query->orderBy('sort_order')->orderBy('name')->paginate(20)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.catalog-product-categories.create', [
            'catalogProductCategory' => new CatalogProductCategory(['status' => CatalogProductCategory::STATUS_ACTIVE, 'sort_order' => 0]),
            'catalogRooms' => CatalogRoom::query()->orderBy('name')->get(),
        ]);
    }

    public function store(CatalogProductCategoryRequest $request): RedirectResponse
    {
        $record = CatalogProductCategory::query()->create($request->validated());
        SyncStoreCategory::syncProductCategory($record);

        return redirect()->route('admin.catalog-product-categories.index')->with('success', 'Product Category saved.');
    }

    public function edit(CatalogProductCategory $catalogProductCategory): View
    {
        return view('admin.catalog-product-categories.edit', [
            'catalogProductCategory' => $catalogProductCategory,
            'catalogRooms' => CatalogRoom::query()->orderBy('name')->get(),
        ]);
    }

    public function update(CatalogProductCategoryRequest $request, CatalogProductCategory $catalogProductCategory): RedirectResponse
    {
        $catalogProductCategory->update($request->validated());
        SyncStoreCategory::syncProductCategory($catalogProductCategory->fresh());

        return redirect()->route('admin.catalog-product-categories.index')->with('success', 'Product Category updated.');
    }

    public function updateStatus(Request $request, CatalogProductCategory $catalogProductCategory): RedirectResponse
    {
        return $this->updateActiveStatus($request, $catalogProductCategory, $catalogProductCategory->name);
    }
}
