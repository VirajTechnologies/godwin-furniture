<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\UpdatesActiveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Branch;
use App\Models\BranchProductPrice;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    use UpdatesActiveStatus;

    public function index(): View
    {
        $products = Product::query()
            ->with('category.parent')
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
            'is_featured' => false,
        ]);

        return view('admin.products.create', [
            'product' => $product,
            'categories' => $this->categories($product),
            'branches' => Branch::query()->orderBy('name')->get(),
            'branchPrices' => collect(),
            'imageUrls' => '',
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $product = Product::query()->create($request->safe()->except(['image_urls', 'branch_prices']));
            $this->syncImages($product, $request->imageUrlList());
            $this->syncBranchPrices($product, $request->input('branch_prices', []));
        });

        return redirect()->route('admin.products.index')->with('success', 'Product saved.');
    }

    public function edit(Product $product): View
    {
        $product->load(['images', 'branchPrices']);

        return view('admin.products.edit', [
            'product' => $product,
            'categories' => $this->categories($product),
            'branches' => Branch::query()->orderBy('name')->get(),
            'branchPrices' => $product->branchPrices->pluck('price', 'branch_id'),
            'imageUrls' => $product->images->pluck('url')->implode("\n"),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        DB::transaction(function () use ($request, $product): void {
            $product->update($request->safe()->except(['code', 'image_urls', 'branch_prices']));
            $this->syncImages($product, $request->imageUrlList());
            $this->syncBranchPrices($product, $request->input('branch_prices', []));
        });

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
            ->with('parent')
            ->whereNotNull('parent_id')
            ->where(function ($query) use ($selectedId) {
                $query->where('status', Category::STATUS_ACTIVE);

                if ($selectedId) {
                    $query->orWhere('id', $selectedId);
                }
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  list<string>  $urls
     */
    private function syncImages(Product $product, array $urls): void
    {
        $product->images()->delete();

        foreach (array_values($urls) as $index => $url) {
            ProductImage::query()->create([
                'product_id' => $product->id,
                'url' => $url,
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $prices
     */
    private function syncBranchPrices(Product $product, array $prices): void
    {
        $product->branchPrices()->delete();

        foreach ($prices as $branchId => $price) {
            if ($price === null || $price === '') {
                continue;
            }

            BranchProductPrice::query()->create([
                'product_id' => $product->id,
                'branch_id' => (int) $branchId,
                'price' => $price,
            ]);
        }
    }
}
