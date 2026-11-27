<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Support\Catalog;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $online = Product::query()
            ->with(['images', 'category.parent', 'activeVariants.images'])
            ->visibleOnStore();

        return view('store.home', [
            'featuredProducts' => (clone $online)->where('is_featured', true)->orderBy('name')->limit(8)->get(),
            'newProducts' => (clone $online)->orderByDesc('id')->limit(8)->get(),
            'rooms' => $this->navigationTree(),
        ]);
    }

    public function furnitureOnRent(): View
    {
        return view('store.furniture-on-rent');
    }

    public function catalog(Request $request): View
    {
        $rooms = $this->navigationTree();
        $facetOptions = $this->catalogFacetOptions();

        $colours = $this->stringList($request->input('colours', []));
        $finishes = $this->stringList($request->input('finishes', []));
        $upholsteries = $this->stringList($request->input('upholsteries', []));
        $materials = $this->stringList($request->input('materials', []));

        // Keep older single-material links working.
        if ($materials === [] && $request->filled('material')) {
            $materials = [$request->string('material')->toString()];
        }

        $colours = array_values(array_intersect($colours, $facetOptions['colours']->all()));
        $finishes = array_values(array_intersect($finishes, $facetOptions['finishes']->all()));
        $upholsteries = array_values(array_intersect($upholsteries, $facetOptions['upholsteries']->all()));
        $materials = array_values(array_intersect($materials, $facetOptions['materials']->all()));

        $query = Product::query()
            ->with(['images', 'category.parent', 'activeVariants.images'])
            ->select('products.*')
            ->selectSub($this->lowestVariantPrice(), 'from_price')
            ->visibleOnStore();

        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->toString().'%';
            $query->where(function ($builder) use ($term): void {
                $builder->where('name', 'like', $term)
                    ->orWhere('code', 'like', $term)
                    ->orWhere('material', 'like', $term);
            });
        }

        $activeCategory = null;
        if ($request->filled('category')) {
            $activeCategory = Category::query()->where('slug', $request->string('category')->toString())->first();
            if ($activeCategory) {
                $query->whereIn('category_id', $this->catalogCategoryIds($activeCategory));
            }
        }

        if ($materials !== []) {
            $query->whereIn('material', $materials);
        }

        $minPrice = $request->filled('min_price') ? (float) $request->input('min_price') : null;
        $maxPrice = $request->filled('max_price') ? (float) $request->input('max_price') : null;
        $hasVariantFilters = $colours !== [] || $finishes !== [] || $upholsteries !== [] || $minPrice !== null || $maxPrice !== null;

        if ($hasVariantFilters) {
            // One matching variant must satisfy every selected facet (and price).
            $query->whereHas('activeVariants', function ($variants) use ($colours, $finishes, $upholsteries, $minPrice, $maxPrice): void {
                if ($colours !== []) {
                    $variants->whereIn('colour', $colours);
                }
                if ($finishes !== []) {
                    $variants->whereIn('finish', $finishes);
                }
                if ($upholsteries !== []) {
                    $variants->whereIn('upholstery', $upholsteries);
                }
                if ($minPrice !== null) {
                    $variants->where('price', '>=', $minPrice);
                }
                if ($maxPrice !== null) {
                    $variants->where('price', '<=', $maxPrice);
                }
            });
        }

        $sort = $request->string('sort')->toString();
        match ($sort) {
            'best_selling' => $query->orderByDesc('is_featured')->orderByDesc('id'),
            'newest' => $query->orderByDesc('id'),
            'price_asc' => $query->orderBy('from_price'),
            'price_desc' => $query->orderByDesc('from_price'),
            'rating' => $query->orderByDesc('is_featured')->orderBy('id'),
            'discount_desc' => $query->selectSub($this->maxVariantDiscount(), 'max_discount')->orderByDesc('max_discount')->orderBy('from_price'),
            'name_asc' => $query->orderBy('name'),
            'name_desc' => $query->orderByDesc('name'),
            default => $query->orderByDesc('is_featured')->orderBy('name'),
        };

        $products = $query->paginate(12)->withQueryString();

        return view('store.catalog', [
            'products' => $products,
            'rooms' => $rooms,
            'activeCategory' => $activeCategory,
            'facetOptions' => $facetOptions,
            'filters' => [
                'q' => $request->string('q')->toString(),
                'category' => $request->string('category')->toString(),
                'colours' => $colours,
                'finishes' => $finishes,
                'upholsteries' => $upholsteries,
                'materials' => $materials,
                'min_price' => $request->input('min_price', ''),
                'max_price' => $request->input('max_price', ''),
                'sort' => $sort !== '' ? $sort : 'popular',
            ],
        ]);
    }

    public function product(string $slug): View
    {
        $product = Product::query()
            ->with(['images', 'category.parent', 'activeVariants.images'])
            ->where('slug', $slug)
            ->visibleOnStore()
            ->firstOrFail();

        $related = Product::query()
            ->with(['images', 'activeVariants.images'])
            ->visibleOnStore()
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

    private function maxVariantDiscount(): \Illuminate\Database\Query\Builder
    {
        return ProductVariant::query()
            ->selectRaw('MAX(CASE WHEN compare_at_price > price AND compare_at_price > 0 THEN ((compare_at_price - price) / compare_at_price) ELSE 0 END)')
            ->whereColumn('product_variants.product_id', 'products.id')
            ->where('status', ProductVariant::STATUS_ACTIVE)
            ->toBase();
    }

    /**
     * Distinct facet values that currently exist on sellable online products.
     *
     * @return array{colours: Collection<int, string>, finishes: Collection<int, string>, upholsteries: Collection<int, string>, materials: Collection<int, string>}
     */
    private function catalogFacetOptions(): array
    {
        $onlineVariants = ProductVariant::query()
            ->where('status', ProductVariant::STATUS_ACTIVE)
            ->whereHas('product', fn ($products) => $products->visibleOnStore());

        return [
            'colours' => (clone $onlineVariants)
                ->where('colour', '!=', '')
                ->distinct()
                ->orderBy('colour')
                ->pluck('colour'),
            'finishes' => (clone $onlineVariants)
                ->where('finish', '!=', '')
                ->distinct()
                ->orderBy('finish')
                ->pluck('finish'),
            'upholsteries' => (clone $onlineVariants)
                ->where('upholstery', '!=', '')
                ->distinct()
                ->orderBy('upholstery')
                ->pluck('upholstery'),
            'materials' => Product::query()
                ->visibleOnStore()
                ->whereNotNull('material')
                ->where('material', '!=', '')
                ->distinct()
                ->orderBy('material')
                ->pluck('material'),
        ];
    }

    /**
     * @param  mixed  $value
     * @return list<string>
     */
    private function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            $value = $value === null || $value === '' ? [] : [$value];
        }

        return array_values(array_unique(array_filter(array_map(
            static fn ($item) => trim((string) $item),
            $value,
        ), static fn (string $item) => $item !== '')));
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
