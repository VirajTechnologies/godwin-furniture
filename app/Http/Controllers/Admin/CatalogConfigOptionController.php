<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\FiltersCatalogMasters;
use App\Http\Controllers\Admin\Concerns\UpdatesActiveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CatalogConfigOptionRequest;
use App\Models\CatalogConfigOption;
use App\Support\Catalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogConfigOptionController extends Controller
{
    use FiltersCatalogMasters, UpdatesActiveStatus;

    public function index(Request $request): View
    {
        $filters = $this->catalogFilters($request, ['family_key', 'field_key']);
        $families = config('catalog.families', []);

        if ($filters['family_key'] !== '' && ! array_key_exists($filters['family_key'], $families)) {
            $filters['family_key'] = '';
        }

        $selectFields = $filters['family_key'] !== ''
            ? Catalog::selectFieldsForFamily($filters['family_key'])
            : [];

        if ($filters['field_key'] !== '' && ! array_key_exists($filters['field_key'], $selectFields)) {
            $filters['field_key'] = '';
        }

        $query = CatalogConfigOption::query();
        $this->applyCatalogFilters($query, $filters);
        $query
            ->when($filters['family_key'] !== '', fn ($builder) => $builder->where('family_key', $filters['family_key']))
            ->when($filters['field_key'] !== '', fn ($builder) => $builder->where('field_key', $filters['field_key']));

        return view('admin.catalog-config-options.index', [
            'filters' => $filters,
            'filterFamilies' => $families,
            'filterFields' => $selectFields,
            'catalogConfigOptions' => $query->orderBy('family_key')->orderBy('field_key')->orderBy('sort_order')->orderBy('name')->paginate(20)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        $families = config('catalog.families', []);
        $defaultFamily = array_key_first($families) ?: 'seat';

        return view('admin.catalog-config-options.create', [
            'catalogConfigOption' => new CatalogConfigOption([
                'family_key' => $defaultFamily,
                'status' => CatalogConfigOption::STATUS_ACTIVE,
                'sort_order' => 0,
            ]),
            'families' => $families,
            'selectFieldsByFamily' => Catalog::selectFieldsByFamily(),
        ]);
    }

    public function store(CatalogConfigOptionRequest $request): RedirectResponse
    {
        CatalogConfigOption::query()->create($request->validated());

        return redirect()->route('admin.catalog-config-options.index')->with('success', 'Configuration option saved.');
    }

    public function edit(CatalogConfigOption $catalogConfigOption): View
    {
        return view('admin.catalog-config-options.edit', [
            'catalogConfigOption' => $catalogConfigOption,
            'families' => config('catalog.families', []),
            'selectFieldsByFamily' => Catalog::selectFieldsByFamily(),
        ]);
    }

    public function update(CatalogConfigOptionRequest $request, CatalogConfigOption $catalogConfigOption): RedirectResponse
    {
        $catalogConfigOption->update($request->validated());

        return redirect()->route('admin.catalog-config-options.index')->with('success', 'Configuration option updated.');
    }

    public function updateStatus(Request $request, CatalogConfigOption $catalogConfigOption): RedirectResponse
    {
        return $this->updateActiveStatus($request, $catalogConfigOption, $catalogConfigOption->name);
    }
}
