<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\FiltersCatalogMasters;
use App\Http\Controllers\Admin\Concerns\UpdatesActiveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductFinishRequest;
use App\Models\ProductFinish;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductFinishController extends Controller
{
    use FiltersCatalogMasters, UpdatesActiveStatus;

    public function index(Request $request): View
    {
        $filters = $this->catalogFilters($request);

        return view('admin.product-finishes.index', [
            'filters' => $filters,
            'productFinishes' => $this->applyCatalogFilters(ProductFinish::query(), $filters)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.product-finishes.create', [
            'productFinish' => new ProductFinish(['status' => ProductFinish::STATUS_ACTIVE, 'sort_order' => 0]),
        ]);
    }

    public function store(ProductFinishRequest $request): RedirectResponse
    {
        ProductFinish::query()->create($request->validated());

        return redirect()->route('admin.product-finishes.index')->with('success', 'Finish saved.');
    }

    public function edit(ProductFinish $productFinish): View
    {
        return view('admin.product-finishes.edit', [
            'productFinish' => $productFinish,
        ]);
    }

    public function update(ProductFinishRequest $request, ProductFinish $productFinish): RedirectResponse
    {
        $productFinish->update($request->validated());

        return redirect()->route('admin.product-finishes.index')->with('success', 'Finish updated.');
    }

    public function updateStatus(Request $request, ProductFinish $productFinish): RedirectResponse
    {
        return $this->updateActiveStatus($request, $productFinish, $productFinish->name);
    }
}
