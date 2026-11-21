<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $online = Product::query()
            ->with(['images', 'category.parent', 'activeVariants.images'])
            ->where('is_online', true)
            ->where('status', Product::STATUS_ACTIVE);

        return view('store.home', [
            'featuredProducts' => (clone $online)->where('is_featured', true)->orderBy('name')->limit(8)->get(),
            'newProducts' => (clone $online)->orderByDesc('id')->limit(8)->get(),
            'rooms' => $this->navigationTree(),
        ]);
    }

    public function catalog(Request $request): View
    {
        $rooms = $this->navigationTree();

        $materials = Product::query()
            ->where('is_online', true)
            ->where('status', Product::STATUS_ACTIVE)
            ->whereNotNull('material')
            ->where('material', '!=', '')
            ->distinct()
            ->orderBy('material')
            ->pluck('material');

        $query = Product::query()
            ->with(['images', 'category.parent', 'activeVariants.images'])
            ->select('products.*')
            ->selectSub($this->lowestVariantPrice(), 'from_price')
            ->where('is_online', true)
            ->where('status', Product::STATUS_ACTIVE);

        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->toString().'%';
            $query->where(function ($builder) use ($term): void {
                $builder->where('name', 'like', $term)
                    ->orWhere('code', 'like', $term)
                    ->orWhere('material', 'like', $term);
            });
        }

        if ($request->filled('category')) {
            $category = Category::query()->where('slug', $request->string('category')->toString())->first();
            if ($category) {
                $query->whereIn('category_id', $this->catalogCategoryIds($category));
            }
        }

        if ($request->filled('material')) {
            $query->where('material', $request->string('material')->toString());
        }

        if ($request->filled('max_price')) {
            $max = (float) $request->input('max_price');
            // A product qualifies when its cheapest variant is within budget.
            $query->whereHas('activeVariants', fn ($variants) => $variants->where('price', '<=', $max));
        }

        $sort = $request->string('sort')->toString();
        match ($sort) {
            'price_asc' => $query->orderBy('from_price'),
            'price_desc' => $query->orderByDesc('from_price'),
            'newest' => $query->orderByDesc('id'),
            default => $query->orderByDesc('is_featured')->orderBy('name'),
        };

        $products = $query->paginate(12)->withQueryString();

        return view('store.catalog', [
            'products' => $products,
            'rooms' => $rooms,
            'materials' => $materials,
            'filters' => [
                'q' => $request->string('q')->toString(),
                'category' => $request->string('category')->toString(),
                'material' => $request->string('material')->toString(),
                'max_price' => $request->input('max_price', 150000),
                'sort' => $sort !== '' ? $sort : 'popular',
            ],
        ]);
    }

    public function product(string $slug): View
    {
        $product = Product::query()
            ->with(['images', 'category.parent', 'activeVariants.images'])
            ->where('slug', $slug)
            ->where('is_online', true)
            ->where('status', Product::STATUS_ACTIVE)
            ->firstOrFail();

        $related = Product::query()
            ->with(['images', 'activeVariants.images'])
            ->where('is_online', true)
            ->where('status', Product::STATUS_ACTIVE)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->orderByDesc('is_featured')
            ->limit(4)
            ->get();

        $variants = $this->sellableVariants($product);

        return view('store.product', [
            'product' => $product,
            'related' => $related,
            'variants' => $variants,
        ]);
    }

    /**
     * Variant payload for the colour / finish / upholstery picker, with the
     * online warehouse quantity so unavailable combinations can be marked.
     *
     * @return list<array<string, mixed>>
     */
    private function sellableVariants(Product $product): array
    {
        $warehouse = Warehouse::primaryForOnline();

        $quantities = $warehouse === null
            ? collect()
            : Stock::query()
                ->whereIn('product_variant_id', $product->activeVariants->pluck('id'))
                ->where('warehouse_id', $warehouse->id)
                ->whereNull('branch_id')
                ->whereNull('agent_id')
                ->pluck('quantity', 'product_variant_id');

        $sharedImages = $product->relationLoaded('images')
            ? $product->images->whereNull('product_variant_id')->pluck('url')->values()->all()
            : $product->sharedImages()->pluck('url')->all();

        return $product->activeVariants
            ->map(function (ProductVariant $variant) use ($quantities, $sharedImages) {
                $variant->loadMissing('images');
                $images = $variant->images->pluck('url')->values()->all();

                return [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'colour' => (string) $variant->colour,
                    'colour_hex' => $variant->colour_hex,
                    'finish' => (string) $variant->finish,
                    'upholstery' => (string) $variant->upholstery,
                    'label' => $variant->label(),
                    'price' => $variant->storePrice(),
                    'mrp' => $variant->mrp(),
                    'available' => (int) ($quantities[$variant->id] ?? 0),
                    'images' => $images !== [] ? $images : $sharedImages,
                ];
            })
            ->sortBy([
                ['price', 'asc'],
                ['id', 'asc'],
            ])
            ->values()
            ->all();
    }

    private function lowestVariantPrice(): \Illuminate\Database\Query\Builder
    {
        return ProductVariant::query()
            ->selectRaw('MIN(price)')
            ->whereColumn('product_variants.product_id', 'products.id')
            ->where('status', ProductVariant::STATUS_ACTIVE)
            ->toBase();
    }

    /**
     * Storefront menu uses room-level categories (Living Room, Bedroom, …),
     * whose children are the product-category leaves products hang off.
     *
     * @return \Illuminate\Support\Collection<int, Category>
     */
    private function navigationTree()
    {
        return Category::query()
            ->with(['children' => fn ($query) => $query
                ->where('status', Category::STATUS_ACTIVE)
                ->orderBy('menu_group')
                ->orderBy('sort_order')
                ->orderBy('name')])
            ->whereNotNull('parent_id')
            ->whereHas('children')
            ->where('status', Category::STATUS_ACTIVE)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return list<int>
     */
    private function catalogCategoryIds(Category $category): array
    {
        $childIds = $category->children()->pluck('id');

        if ($childIds->isEmpty()) {
            return [$category->id];
        }

        $leafIds = Category::query()->whereIn('parent_id', $childIds)->pluck('id');

        // Project → rooms → leaves, or room → leaves.
        return $leafIds->isNotEmpty()
            ? $leafIds->all()
            : $childIds->all();
    }
}
