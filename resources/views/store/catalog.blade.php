@extends('layouts.store')

@section('title', $activeCategory ? $activeCategory->name : 'Furniture Catalog')
@section('body_class', 'gw-theme-body')

@section('content')
    @include('store.partials.header')

    @php
        $preservedFilters = array_filter([
            'q' => $filters['q'] !== '' ? $filters['q'] : null,
            'colours' => $filters['colours'] !== [] ? $filters['colours'] : null,
            'finishes' => $filters['finishes'] !== [] ? $filters['finishes'] : null,
            'upholsteries' => $filters['upholsteries'] !== [] ? $filters['upholsteries'] : null,
            'materials' => $filters['materials'] !== [] ? $filters['materials'] : null,
            'min_price' => $filters['min_price'] !== '' ? $filters['min_price'] : null,
            'max_price' => $filters['max_price'] !== '' ? $filters['max_price'] : null,
            'sort' => $filters['sort'] !== 'popular' ? $filters['sort'] : null,
        ], fn ($value) => $value !== null && $value !== '');

        $pageHeading = $activeCategory
            ? (strcasecmp($activeCategory->name, 'Dining Room') === 0 ? 'Dining' : $activeCategory->name)
            : ($filters['q'] !== '' ? 'Search: "'.$filters['q'].'"' : 'All Products');
    @endphp

    <section class="gw-wrap gw-catalog-wrap">
        <nav aria-label="breadcrumb" class="gw-cat-crumbs">
            <a href="{{ route('store.home') }}">Home</a>
            <span>/</span>
            @if ($activeCategory && $activeCategory->parent)
                <a href="{{ route('store.catalog', ['category' => $activeCategory->parent->slug]) }}">
                    {{ strcasecmp($activeCategory->parent->name, 'Dining Room') === 0 ? 'Dining' : $activeCategory->parent->name }}
                </a>
                <span>/</span>
            @endif
            <span>{{ $pageHeading }}</span>
        </nav>

        <h1 class="gw-cat-heading">{{ $pageHeading }}</h1>

        <div class="gw-cat-topbar">
            <div class="gw-cat-count">
                {{ $products->total() }} {{ $products->total() === 1 ? 'product' : 'products' }} · Godwin catalogue
            </div>

            <div class="d-flex align-items-center gap-2">
                <button type="button"
                        class="gw-btn gw-btn-sm gw-btn-outline gw-filter-mobile-btn"
                        onclick="document.getElementById('gwCatalogAside').classList.toggle('open')">
                    Filters
                </button>

                <form method="GET" action="{{ route('store.catalog') }}" class="m-0">
                    @foreach (request()->except('sort', 'page') as $key => $value)
                        @if (is_array($value))
                            @foreach ($value as $item)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <select name="sort" class="gw-catalog-sort" aria-label="Sort products" onchange="this.form.submit()">
                        <option value="popular" @selected($filters['sort'] === 'popular')>Recommended</option>
                        <option value="best_selling" @selected($filters['sort'] === 'best_selling')>Best selling</option>
                        <option value="newest" @selected($filters['sort'] === 'newest')>New arrivals</option>
                        <option value="price_asc" @selected($filters['sort'] === 'price_asc')>Price: low to high</option>
                        <option value="price_desc" @selected($filters['sort'] === 'price_desc')>Price: high to low</option>
                        <option value="rating" @selected($filters['sort'] === 'rating')>Customer ratings</option>
                        <option value="discount_desc" @selected($filters['sort'] === 'discount_desc')>Discount: high to low</option>
                        <option value="name_asc" @selected($filters['sort'] === 'name_asc')>A–Z</option>
                        <option value="name_desc" @selected($filters['sort'] === 'name_desc')>Z–A</option>
                    </select>
                </form>
            </div>
        </div>

        <div class="gw-cat-layout">
            <aside class="gw-aside" id="gwCatalogAside">
                <form method="GET" action="{{ route('store.catalog') }}" id="gwFilterForm">
                    @if ($filters['category'] !== '')
                        <input type="hidden" name="category" value="{{ $filters['category'] }}">
                    @endif
                    @if ($filters['q'] !== '')
                        <input type="hidden" name="q" value="{{ $filters['q'] }}">
                    @endif
                    <input type="hidden" name="sort" value="{{ $filters['sort'] }}">

                    {{-- Price (₹) --}}
                    <details class="gw-filter-group" open>
                        <summary>Price (₹)</summary>
                        <div class="gw-price-inputs">
                            <input type="number"
                                   name="min_price"
                                   id="gwMinPrice"
                                   value="{{ $filters['min_price'] }}"
                                   placeholder="Min"
                                   min="0"
                                   step="500">
                            <input type="number"
                                   name="max_price"
                                   id="gwMaxPrice"
                                   value="{{ $filters['max_price'] }}"
                                   placeholder="Max"
                                   min="0"
                                   step="500">
                        </div>
                        <div class="gw-price-chips">
                            <button type="button"
                                    class="gw-price-chip {{ (string) $filters['min_price'] === '0' && (string) $filters['max_price'] === '10000' ? 'is-active' : '' }}"
                                    onclick="gwSetPriceRange(0, 10000)">₹0k–10k</button>
                            <button type="button"
                                    class="gw-price-chip {{ (string) $filters['min_price'] === '10000' && (string) $filters['max_price'] === '25000' ? 'is-active' : '' }}"
                                    onclick="gwSetPriceRange(10000, 25000)">₹10k–25k</button>
                            <button type="button"
                                    class="gw-price-chip {{ (string) $filters['min_price'] === '25000' && (string) $filters['max_price'] === '50000' ? 'is-active' : '' }}"
                                    onclick="gwSetPriceRange(25000, 50000)">₹25k–50k</button>
                            <button type="button"
                                    class="gw-price-chip {{ (string) $filters['min_price'] === '50000' && (string) $filters['max_price'] === '' ? 'is-active' : '' }}"
                                    onclick="gwSetPriceRange(50000, '')">Above ₹50k</button>
                        </div>
                    </details>

                    {{-- Product type / Categories --}}
                    <details class="gw-filter-group" open>
                        <summary>Product type</summary>
                        <div class="gw-filter-list">
                            @foreach ($rooms as $room)
                                @php
                                    $roomLabel = strcasecmp($room->name, 'Dining Room') === 0 ? 'Dining' : $room->name;
                                @endphp
                                <a href="{{ route('store.catalog', array_merge($preservedFilters, ['category' => $room->slug])) }}"
                                   class="gw-aside-cat-link {{ $filters['category'] === $room->slug ? 'is-active' : '' }}">
                                    {{ $roomLabel }}
                                </a>
                                @foreach ($room->children as $child)
                                    <a href="{{ route('store.catalog', array_merge($preservedFilters, ['category' => $child->slug])) }}"
                                       class="gw-aside-cat-link gw-aside-sub {{ $filters['category'] === $child->slug ? 'is-active' : '' }}">
                                        {{ $child->name }}
                                    </a>
                                @endforeach
                            @endforeach
                        </div>
                    </details>

                    {{-- Metal finish --}}
                    @if ($facetOptions['finishes']->isNotEmpty())
                        <details class="gw-filter-group" open>
                            <summary>Metal finish</summary>
                            <div class="gw-filter-list">
                                @foreach ($facetOptions['finishes'] as $finish)
                                    <label class="gw-aside-check">
                                        <input type="checkbox" name="finishes[]" value="{{ $finish }}" @checked(in_array($finish, $filters['finishes'], true)) onchange="this.form.submit()">
                                        <span>{{ $finish }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </details>
                    @endif

                    {{-- Colour --}}
                    @if ($facetOptions['colours']->isNotEmpty())
                        <details class="gw-filter-group" open>
                            <summary>Colour</summary>
                            <div class="gw-filter-list">
                                @foreach ($facetOptions['colours'] as $colour)
                                    <label class="gw-aside-check">
                                        <input type="checkbox" name="colours[]" value="{{ $colour }}" @checked(in_array($colour, $filters['colours'], true)) onchange="this.form.submit()">
                                        <span>{{ $colour }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </details>
                    @endif

                    {{-- Upholstery --}}
                    @if ($facetOptions['upholsteries']->isNotEmpty())
                        <details class="gw-filter-group" open>
                            <summary>Upholstery</summary>
                            <div class="gw-filter-list">
                                @foreach ($facetOptions['upholsteries'] as $upholstery)
                                    <label class="gw-aside-check">
                                        <input type="checkbox" name="upholsteries[]" value="{{ $upholstery }}" @checked(in_array($upholstery, $filters['upholsteries'], true)) onchange="this.form.submit()">
                                        <span>{{ $upholstery }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </details>
                    @endif

                    {{-- Material & Build --}}
                    @if ($facetOptions['materials']->isNotEmpty())
                        <details class="gw-filter-group" open>
                            <summary>Material &amp; build</summary>
                            <div class="gw-filter-list">
                                @foreach ($facetOptions['materials'] as $material)
                                    <label class="gw-aside-check">
                                        <input type="checkbox" name="materials[]" value="{{ $material }}" @checked(in_array($material, $filters['materials'], true)) onchange="this.form.submit()">
                                        <span>{{ $material }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </details>
                    @endif

                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <a href="{{ route('store.catalog') }}" class="gw-btn gw-btn-sm gw-btn-outline">Clear all</a>
                        <button type="submit" class="gw-btn gw-btn-sm">Apply</button>
                    </div>
                </form>
            </aside>

            <div>
                <div class="row g-4">
                    @forelse ($products as $product)
                        @include('store.partials.product-card', ['product' => $product])
                    @empty
                        <div class="col-12">
                            <div class="gw-panel p-5 text-center">
                                <h3 class="gw-panel-subtitle mb-2">No products match these filters</h3>
                                <p class="gw-page-sub mb-4">Try clearing the filters or choosing another room.</p>
                                <a href="{{ route('store.catalog') }}" class="gw-btn gw-btn-sm">Clear all</a>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if ($products->hasPages())
                    <div class="mt-4 gw-pagination">{{ $products->links() }}</div>
                @endif
            </div>
        </div>
    </section>

    @include('store.partials.footer')
    @include('store.partials.whatsapp')
@endsection

@push('scripts')
<script>
    function gwSetPriceRange(minVal, maxVal) {
        var minEl = document.getElementById('gwMinPrice');
        var maxEl = document.getElementById('gwMaxPrice');
        var form = document.getElementById('gwFilterForm');
        if (minEl) minEl.value = minVal;
        if (maxEl) maxEl.value = maxVal;
        if (form) form.submit();
    }
</script>
@endpush
