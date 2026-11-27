<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\FiltersCatalogMasters;
use App\Http\Controllers\Admin\Concerns\UpdatesActiveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductUpholsteryRequest;
use App\Models\ProductUpholstery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductUpholsteryController extends Controller
{
    use FiltersCatalogMasters, UpdatesActiveStatus;

    public function index(Request $request): View
    {
        $filters = $this->catalogFilters($request);

        return view('admin.product-upholsteries.index', [
            'filters' => $filters,
            'productUpholsteries' => $this->applyCatalogFilters(ProductUpholstery::query(), $filters)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.product-upholsteries.create', [
            'productUpholstery' => new ProductUpholstery(['status' => ProductUpholstery::STATUS_ACTIVE, 'sort_order' => 0]),
        ]);
    }

    public function store(ProductUpholsteryRequest $request): RedirectResponse
    {
        ProductUpholstery::query()->create($request->validated());

        return redirect()->route('admin.product-upholsteries.index')->with('success', 'Upholstery saved.');
    }

    public function edit(ProductUpholstery $productUpholstery): View
    {
        return view('admin.product-upholsteries.edit', [
            'productUpholstery' => $productUpholstery,
        ]);
    }

    public function update(ProductUpholsteryRequest $request, ProductUpholstery $productUpholstery): RedirectResponse
    {
        $productUpholstery->update($request->validated());

        return redirect()->route('admin.product-upholsteries.index')->with('success', 'Upholstery updated.');
    }

    public function updateStatus(Request $request, ProductUpholstery $productUpholstery): RedirectResponse
    {
        return $this->updateActiveStatus($request, $productUpholstery, $productUpholstery->name);
    }
}
