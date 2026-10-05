<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\View as ViewFacade;

class HomeController extends Controller
{
    public function __construct()
    {
        ViewFacade::share('storeMenu', $this->menu());
    }

    public function index(): View
    {
        $online = Product::query()
            ->with(['images', 'category.parent'])
            ->where('is_online', true)
            ->where('status', Product::STATUS_ACTIVE);

        return view('store.home', [
            'featuredProducts' => (clone $online)->where('is_featured', true)->orderBy('name')->limit(8)->get(),
            'newProducts' => (clone $online)->orderByDesc('id')->limit(8)->get(),
            'rooms' => Category::query()
                ->with(['children' => fn ($query) => $query
                    ->where('status', Category::STATUS_ACTIVE)
                    ->orderBy('menu_group')
                    ->orderBy('sort_order')
                    ->orderBy('name')])
                ->whereNull('parent_id')
                ->where('status', Category::STATUS_ACTIVE)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function catalog(Request $request): View
    {
        $rooms = Category::query()
            ->with(['children' => fn ($query) => $query
                ->where('status', Category::STATUS_ACTIVE)
                ->orderBy('menu_group')
                ->orderBy('sort_order')
                ->orderBy('name')])
            ->whereNull('parent_id')
            ->where('status', Category::STATUS_ACTIVE)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $materials = Product::query()
            ->where('is_online', true)
            ->where('status', Product::STATUS_ACTIVE)
            ->whereNotNull('material')
            ->where('material', '!=', '')
            ->distinct()
            ->orderBy('material')
            ->pluck('material');

        $query = Product::query()
            ->with(['images', 'category.parent'])
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
                if ($category->isRoom()) {
                    $query->whereIn('category_id', $category->children()->pluck('id'));
                } else {
                    $query->where('category_id', $category->id);
                }
            }
        }

        if ($request->filled('material')) {
            $query->where('material', $request->string('material')->toString());
        }

        if ($request->filled('max_price')) {
            $max = (float) $request->input('max_price');
            $query->whereRaw('COALESCE(online_price, selling_price) <= ?', [$max]);
        }

        $sort = $request->string('sort')->toString();
        match ($sort) {
            'price_asc' => $query->orderByRaw('COALESCE(online_price, selling_price) asc'),
            'price_desc' => $query->orderByRaw('COALESCE(online_price, selling_price) desc'),
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
            ->with(['images', 'category.parent'])
            ->where('slug', $slug)
            ->where('is_online', true)
            ->where('status', Product::STATUS_ACTIVE)
            ->firstOrFail();

        $related = Product::query()
            ->with('images')
            ->where('is_online', true)
            ->where('status', Product::STATUS_ACTIVE)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->orderByDesc('is_featured')
            ->limit(4)
            ->get();

        return view('store.product', [
            'product' => $product,
            'related' => $related,
        ]);
    }

    public function cart(): View
    {
        return view('store.cart');
    }

    public function login(): View
    {
        return view('store.login');
    }

    public function register(): View
    {
        return view('store.register');
    }

        private function menu()
    {
        return Category::query()
            ->with(['children' => fn ($query) => $query
                ->where('status', Category::STATUS_ACTIVE)
                ->orderBy('menu_group')
                ->orderBy('sort_order')
                ->orderBy('name')])
            ->whereNull('parent_id')
            ->where('status', Category::STATUS_ACTIVE)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
