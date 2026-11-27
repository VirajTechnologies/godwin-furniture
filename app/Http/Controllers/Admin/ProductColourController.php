<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\FiltersCatalogMasters;
use App\Http\Controllers\Admin\Concerns\UpdatesActiveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductColourRequest;
use App\Models\ProductColour;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductColourController extends Controller
{
    use FiltersCatalogMasters, UpdatesActiveStatus;

    public function index(Request $request): View
    {
        $filters = $this->catalogFilters($request);

        return view('admin.product-colours.index', [
            'filters' => $filters,
            'productColours' => $this->applyCatalogFilters(ProductColour::query(), $filters)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.product-colours.create', [
            'productColour' => new ProductColour(['status' => ProductColour::STATUS_ACTIVE, 'sort_order' => 0, 'hex' => '#888888']),
        ]);
    }

    public function store(ProductColourRequest $request): RedirectResponse
    {
        ProductColour::query()->create($request->validated());

        return redirect()->route('admin.product-colours.index')->with('success', 'Colour saved.');
    }

    public function edit(ProductColour $productColour): View
    {
        return view('admin.product-colours.edit', [
            'productColour' => $productColour,
        ]);
    }

    public function update(ProductColourRequest $request, ProductColour $productColour): RedirectResponse
    {
        $productColour->update($request->validated());

        return redirect()->route('admin.product-colours.index')->with('success', 'Colour updated.');
    }

    public function updateStatus(Request $request, ProductColour $productColour): RedirectResponse
    {
        return $this->updateActiveStatus($request, $productColour, $productColour->name);
    }
}
