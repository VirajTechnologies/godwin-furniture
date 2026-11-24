<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\FiltersCatalogMasters;
use App\Http\Controllers\Admin\Concerns\UpdatesActiveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DesignTypeRequest;
use App\Models\CatalogProductCategory;
use App\Models\DesignType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DesignTypeController extends Controller
{
    use FiltersCatalogMasters, UpdatesActiveStatus;

    public function index(Request $request): View
    {
        $filters = $this->catalogFilters($request, ['catalog_product_category_id']);
        $productCategories = CatalogProductCategory::query()->with('room')->orderBy('name')->get();

        if ($filters['catalog_product_category_id'] !== '' && ! $productCategories->contains('id', (int) $filters['catalog_product_category_id'])) {
            $filters['catalog_product_category_id'] = '';
        }

        $query = DesignType::query()->with(['productCategory']);
        $this->applyCatalogFilters($query, $filters);
        $query->when(
            $filters['catalog_product_category_id'] !== '',
            fn ($builder) => $builder->where('catalog_product_category_id', (int) $filters['catalog_product_category_id'])
        );

        return view('admin.design-types.index', [
            'filters' => $filters,
            'filterProductCategories' => $productCategories,
            'designTypes' => $query->orderBy('sort_order')->orderBy('name')->paginate(20)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.design-types.create', [
            'designType' => new DesignType(['status' => DesignType::STATUS_ACTIVE, 'sort_order' => 0]),
            'catalogProductCategories' => CatalogProductCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function store(DesignTypeRequest $request): RedirectResponse
    {
        DesignType::query()->create($request->validated());

        return redirect()->route('admin.design-types.index')->with('success', 'Design Type saved.');
    }

    public function edit(DesignType $designType): View
    {
        return view('admin.design-types.edit', [
            'designType' => $designType,
            'catalogProductCategories' => CatalogProductCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function update(DesignTypeRequest $request, DesignType $designType): RedirectResponse
    {
        $designType->update($request->validated());

        return redirect()->route('admin.design-types.index')->with('success', 'Design Type updated.');
    }

    public function updateStatus(Request $request, DesignType $designType): RedirectResponse
    {
        return $this->updateActiveStatus($request, $designType, $designType->name);
    }
}
