<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\FiltersCatalogMasters;
use App\Http\Controllers\Admin\Concerns\UpdatesActiveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductModelRequest;
use App\Models\CatalogProductCategory;
use App\Models\ProductModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductModelController extends Controller
{
    use FiltersCatalogMasters, UpdatesActiveStatus;

    public function index(Request $request): View
    {
        $filters = $this->catalogFilters($request, ['catalog_product_category_id']);
        $productCategories = CatalogProductCategory::query()->with('room')->orderBy('name')->get();

        if ($filters['catalog_product_category_id'] !== '' && ! $productCategories->contains('id', (int) $filters['catalog_product_category_id'])) {
            $filters['catalog_product_category_id'] = '';
        }

        $query = ProductModel::query()->with(['productCategory']);
        $this->applyCatalogFilters($query, $filters);
        $query->when(
            $filters['catalog_product_category_id'] !== '',
            fn ($builder) => $builder->where('catalog_product_category_id', (int) $filters['catalog_product_category_id'])
        );

        return view('admin.product-models.index', [
            'filters' => $filters,
            'filterProductCategories' => $productCategories,
            'productModels' => $query->orderBy('sort_order')->orderBy('name')->paginate(20)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.product-models.create', [
            'productModel' => new ProductModel(['status' => ProductModel::STATUS_ACTIVE, 'sort_order' => 0]),
            'catalogProductCategories' => CatalogProductCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function store(ProductModelRequest $request): RedirectResponse
    {
        ProductModel::query()->create($request->validated());

        return redirect()->route('admin.product-models.index')->with('success', 'Product Model saved.');
    }

    public function edit(ProductModel $productModel): View
    {
        return view('admin.product-models.edit', [
            'productModel' => $productModel,
            'catalogProductCategories' => CatalogProductCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function update(ProductModelRequest $request, ProductModel $productModel): RedirectResponse
    {
        $productModel->update($request->validated());

        return redirect()->route('admin.product-models.index')->with('success', 'Product Model updated.');
    }

    public function updateStatus(Request $request, ProductModel $productModel): RedirectResponse
    {
        return $this->updateActiveStatus($request, $productModel, $productModel->name);
    }
}
