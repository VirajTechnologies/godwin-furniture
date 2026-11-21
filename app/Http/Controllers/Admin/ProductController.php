<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\UpdatesActiveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Agent;
use App\Models\AgentProductTerm;
use App\Models\Branch;
use App\Models\BranchProductPrice;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Support\Catalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    use UpdatesActiveStatus;

    public function index(Request $request): View
    {
        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'status' => $request->string('status')->toString(),
            'online' => $request->string('online')->toString(),
            'featured' => $request->string('featured')->toString(),
            'project_category' => $request->string('project_category')->toString(),
            'room' => $request->string('room')->toString(),
        ];

        if (! in_array($filters['status'], ['', Product::STATUS_ACTIVE, Product::STATUS_INACTIVE], true)) {
            $filters['status'] = '';
        }

        if (! in_array($filters['online'], ['', '1', '0'], true)) {
            $filters['online'] = '';
        }

        if (! in_array($filters['featured'], ['', '1', '0'], true)) {
            $filters['featured'] = '';
        }

        $projects = Catalog::projects();
        if ($filters['project_category'] !== '' && ! in_array($filters['project_category'], $projects, true)) {
            $filters['project_category'] = '';
        }

        $rooms = $this->filterRooms($filters['project_category'] !== '' ? $filters['project_category'] : null);
        if ($filters['room'] !== '' && ! in_array($filters['room'], $rooms, true)) {
            $filters['room'] = '';
        }

        $products = Product::query()
            ->with(['variants', 'category.parent'])
            ->withSum('stocks as stock_total', 'quantity')
            ->when($filters['q'] !== '', function ($query) use ($filters): void {
                $query->where(function ($builder) use ($filters): void {
                    $builder->where('name', 'like', '%'.$filters['q'].'%')
                        ->orWhere('code', 'like', '%'.$filters['q'].'%');
                });
            })
            ->when($filters['status'] !== '', fn ($query) => $query->where('status', $filters['status']))
            ->when($filters['online'] !== '', fn ($query) => $query->where('is_online', $filters['online'] === '1'))
            ->when($filters['featured'] !== '', fn ($query) => $query->where('is_featured', $filters['featured'] === '1'))
            ->when($filters['project_category'] !== '', fn ($query) => $query->where('project_category', $filters['project_category']))
            ->when($filters['room'] !== '', fn ($query) => $query->where('room', $filters['room']))
            ->orderBy('name')
            ->get();

        return view('admin.products.index', [
            'products' => $products,
            'filters' => $filters,
            'filterProjects' => $projects,
            'filterRooms' => $rooms,
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

        $product->setRelation('variants', collect());
        $product->setRelation('images', collect());

        return view('admin.products.create', $this->formData($product));
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $data = $request->validated();
            $category = Catalog::leafCategory($data['project_category'], $data['room'], $data['product_category']);
            $family = Catalog::family($data['project_category'], $data['product_category']);

            $product = Product::query()->create(array_merge(
                Arr::except($data, ['specs', 'variants', 'image_urls', 'branch_prices', 'agent_terms']),
                [
                    'category_id' => $category->id,
                    'family' => $family,
                    'specs' => $this->specsFor($family, $data['specs'] ?? []),
                ],
            ));

            $this->syncImages($product, $request->imageUrlList());
            $this->syncVariants($product, $data['variants']);
            $this->syncBranchPrices($product, $data['branch_prices'] ?? []);
            $this->syncAgentTerms($product, $data['agent_terms'] ?? []);
        });

        return redirect()->route('admin.products.index')
            ->with('success', 'Product saved. Open it again to set showroom and shop prices.');
    }

    public function edit(Product $product): View
    {
        $product->load(['images', 'variants.images', 'variants.stocks', 'variants.branchPrices', 'variants.agentTerms']);

        return view('admin.products.edit', $this->formData($product));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        DB::transaction(function () use ($request, $product): void {
            $data = $request->validated();
            $category = Catalog::leafCategory($data['project_category'], $data['room'], $data['product_category']);
            $family = Catalog::family($data['project_category'], $data['product_category']);

            $product->update(array_merge(
                Arr::except($data, ['code', 'specs', 'variants', 'image_urls', 'branch_prices', 'agent_terms']),
                [
                    'category_id' => $category->id,
                    'family' => $family,
                    'specs' => $this->specsFor($family, $data['specs'] ?? []),
                ],
            ));

            $this->syncImages($product, $request->imageUrlList());
            $this->syncVariants($product, $data['variants']);
            $this->syncBranchPrices($product, $data['branch_prices'] ?? []);
            $this->syncAgentTerms($product, $data['agent_terms'] ?? []);
        });

        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    public function updateStatus(Request $request, Product $product): RedirectResponse
    {
        return $this->updateActiveStatus($request, $product, $product->name);
    }

    /**
     * @return list<string>
     */
    private function filterRooms(?string $project = null): array
    {
        $rooms = [];

        foreach (Catalog::projects() as $projectName) {
            if ($project !== null && $projectName !== $project) {
                continue;
            }

            foreach (Catalog::rooms($projectName) as $room) {
                $rooms[$room] = $room;
            }
        }

        $rooms = array_values($rooms);
        sort($rooms, SORT_NATURAL | SORT_FLAG_CASE);

        return $rooms;
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Product $product): array
    {
        return [
            'product' => $product,
            'catalog' => Catalog::clientConfig(),
            // Every branch and shop is listed: the form rewrites all overrides, so a hidden column would drop its price.
            'branches' => Branch::query()->orderBy('name')->get(),
            'agents' => Agent::query()->orderBy('name')->get(),
            'branchPrices' => $product->variants->mapWithKeys(
                fn (ProductVariant $variant) => [$variant->id => $variant->branchPrices->pluck('price', 'branch_id')],
            ),
            'agentTerms' => $product->variants->mapWithKeys(
                fn (ProductVariant $variant) => [$variant->id => $variant->agentTerms->keyBy('agent_id')],
            ),
            'imageUrls' => $product->images->whereNull('product_variant_id')->pluck('url')->implode("\n"),
            'nextSequence' => (int) Product::query()->max('id') + 1,
        ];
    }

    /**
     * Keep only the fields the detected family declares, in config order.
     *
     * @param  array<string, mixed>  $posted
     * @return array<string, string>
     */
    private function specsFor(string $family, array $posted): array
    {
        $specs = [];

        foreach (Catalog::familyConfig($family)['fields'] as $field) {
            $value = $posted[$field['label']] ?? null;

            if ($value !== null && $value !== '') {
                $specs[$field['label']] = (string) $value;
            }
        }

        return $specs;
    }

    /**
     * Shared product gallery (used when a variant has no photos).
     *
     * @param  list<string>  $urls
     */
    private function syncImages(Product $product, array $urls): void
    {
        $product->sharedImages()->delete();

        foreach (array_values($urls) as $index => $url) {
            ProductImage::query()->create([
                'product_id' => $product->id,
                'product_variant_id' => null,
                'url' => $url,
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * @param  list<string>  $urls
     */
    private function syncVariantImages(Product $product, ProductVariant $variant, array $urls): void
    {
        $variant->images()->delete();

        foreach (array_values($urls) as $index => $url) {
            ProductImage::query()->create([
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'url' => $url,
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * @return list<string>
     */
    private function urlLines(?string $raw): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $raw) ?: [])
            ->map(fn ($url) => trim($url))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function syncVariants(Product $product, array $rows): void
    {
        $usesUpholstery = Catalog::usesUpholstery($product->family);
        $existing = $product->variants()->get()->keyBy(
            fn (ProductVariant $variant) => $this->variantKey($variant->colour, $variant->finish, (string) $variant->upholstery),
        );
        $keptIds = [];

        foreach ($rows as $row) {
            $colour = (string) $row['colour'];
            $finish = (string) $row['finish'];
            $upholstery = $usesUpholstery ? (string) ($row['upholstery'] ?? '') : '';
            $mrp = $row['compare_at_price'] ?? null;

            $attributes = [
                'sku' => Catalog::skuFor($product->code, $colour, $finish, $upholstery === '' ? null : $upholstery),
                'colour' => $colour,
                'colour_hex' => Catalog::colourHex($colour),
                'finish' => $finish,
                'upholstery' => $upholstery,
                'price' => $row['price'],
                'compare_at_price' => $mrp === null || $mrp === '' ? null : $mrp,
                'status' => $row['status'] ?? ProductVariant::STATUS_ACTIVE,
            ];

            $variant = $existing->get($this->variantKey($colour, $finish, $upholstery));

            if ($variant !== null) {
                $variant->update($attributes);
            } else {
                $variant = $product->variants()->create($attributes);
            }

            $this->syncVariantImages($product, $variant, $this->urlLines($row['image_urls'] ?? null));
            $keptIds[] = $variant->id;
        }

        foreach ($existing as $variant) {
            if (in_array($variant->id, $keptIds, true)) {
                continue;
            }

            // Stock counts and order history point at the variant, so retire it instead of deleting it.
            if ($variant->stocks()->exists() || OrderItem::query()->where('product_variant_id', $variant->id)->exists()) {
                $variant->update(['status' => ProductVariant::STATUS_INACTIVE]);

                continue;
            }

            $variant->delete();
        }

        $product->load('variants');
    }

    private function variantKey(string $colour, string $finish, string $upholstery): string
    {
        return $colour.'|'.$finish.'|'.$upholstery;
    }

    /**
     * @param  array<int|string, array<int|string, mixed>>  $prices
     */
    private function syncBranchPrices(Product $product, array $prices): void
    {
        $variantIds = $product->variants->pluck('id')->all();
        BranchProductPrice::query()->whereIn('product_variant_id', $variantIds)->delete();

        foreach ($prices as $variantId => $branches) {
            if (! is_array($branches) || ! in_array((int) $variantId, $variantIds, true)) {
                continue;
            }

            foreach ($branches as $branchId => $price) {
                if ($price === null || $price === '') {
                    continue;
                }

                BranchProductPrice::query()->create([
                    'product_variant_id' => (int) $variantId,
                    'branch_id' => (int) $branchId,
                    'price' => $price,
                ]);
            }
        }
    }

    /**
     * @param  array<int|string, array<int|string, mixed>>  $terms
     */
    private function syncAgentTerms(Product $product, array $terms): void
    {
        $variantIds = $product->variants->pluck('id')->all();
        AgentProductTerm::query()->whereIn('product_variant_id', $variantIds)->delete();

        foreach ($terms as $variantId => $agents) {
            if (! is_array($agents) || ! in_array((int) $variantId, $variantIds, true)) {
                continue;
            }

            foreach ($agents as $agentId => $term) {
                if (! is_array($term)) {
                    continue;
                }

                $price = $term['price'] ?? null;
                $commission = $term['commission_percent'] ?? null;
                $hasPrice = $price !== null && $price !== '';
                $hasCommission = $commission !== null && $commission !== '';

                if (! $hasPrice && ! $hasCommission) {
                    continue;
                }

                AgentProductTerm::query()->create([
                    'product_variant_id' => (int) $variantId,
                    'agent_id' => (int) $agentId,
                    'price' => $hasPrice ? $price : null,
                    'commission_percent' => $hasCommission ? $commission : null,
                ]);
            }
        }
    }
}
