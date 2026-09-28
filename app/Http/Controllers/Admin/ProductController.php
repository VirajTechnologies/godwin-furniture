<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\UpdatesActiveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    use UpdatesActiveStatus;

    public function index(): View
    {
        $products = Product::query()
            ->with('category')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.products.index', [
            'products' => $products,
        ]);
    }

    public function create(): View
    {
        $product = new Product([
            'status' => Product::STATUS_ACTIVE,
            'unit' => 'Piece',
            'is_online' => false,
        ]);

        return view('admin.products.create', [
            'product' => $product,
            'categories' => $this->categories($product),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        Product::query()->create($request->validated());

        return redirect()->route('admin.products.index')->with('success', 'Product saved.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product,
            'categories' => $this->categories($product),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->safe()->except('code'));

        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    public function updateStatus(Request $request, Product $product): RedirectResponse
    {
        return $this->updateActiveStatus($request, $product, $product->name);
    }

    private function categories(Product $product)
    {
        $selectedId = (int) old('category_id', $product->category_id) ?: null;

        return Category::query()
            ->where(function ($query) use ($selectedId) {
                $query->where('status', Category::STATUS_ACTIVE);

                if ($selectedId) {
                    $query->orWhere('id', $selectedId);
                }
            })
            ->orderBy('name')
            ->get();
    }
}
