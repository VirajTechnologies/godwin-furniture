@extends('layouts.store')

@section('title', 'Furniture Catalog')
@section('body_class', 'bg-light')

@push('scripts')
<script>
    function slideProductCarousel(carouselId, direction, event) {
        if (event) { event.preventDefault(); event.stopPropagation(); }
        var elem = document.getElementById(carouselId);
        if (!elem || typeof bootstrap === 'undefined') return;
        var carousel = bootstrap.Carousel.getOrCreateInstance(elem, { interval: false });
        direction === 'prev' ? carousel.prev() : carousel.next();
    }
    function goToProductSlide(carouselId, index, event) {
        if (event) { event.preventDefault(); event.stopPropagation(); }
        var elem = document.getElementById(carouselId);
        if (!elem || typeof bootstrap === 'undefined') return;
        bootstrap.Carousel.getOrCreateInstance(elem, { interval: false }).to(index);
    }
</script>
@endpush

@section('content')
    @include('store.partials.header')

    <section class="bg-white border-bottom py-3">
        <div class="container-fluid px-4 px-lg-5">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb m-0 font-heading small">
                    <li class="breadcrumb-item"><a href="{{ route('store.home') }}" class="text-muted text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active text-amber fw-bold" aria-current="page">Furniture Catalog</li>
                </ol>
            </nav>
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mt-2">
                <div>
                    <h2 class="font-heading fw-bold text-dark m-0">Furniture Catalog</h2>
                    <p class="text-muted small m-0">Browse the factory collection by room, material, and price.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="container-fluid px-4 px-lg-5 my-4">
        <div class="row g-4">
            <div class="col-12 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 80px; z-index: 10;">
                    <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-3">
                        <h6 class="font-heading fw-bold text-dark m-0"><i class="fas fa-sliders-h text-amber me-2"></i> Filter Products</h6>
                        <a href="{{ route('store.catalog') }}" class="text-amber small text-decoration-none fw-semibold">Reset All</a>
                    </div>

                    @php
                        $preservedFilters = array_filter([
                            'q' => $filters['q'] !== '' ? $filters['q'] : null,
                            'material' => $filters['material'] !== '' ? $filters['material'] : null,
                            'max_price' => (string) $filters['max_price'] !== '150000' ? $filters['max_price'] : null,
                            'sort' => $filters['sort'] !== 'popular' ? $filters['sort'] : null,
                        ], fn ($value) => $value !== null && $value !== '');
                    @endphp

                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="font-heading fw-bold small text-dark m-0">Categories</label>
                            @if ($filters['category'] !== '')
                                <a href="{{ route('store.catalog', $preservedFilters) }}" class="text-muted small text-decoration-none">Clear</a>
                            @endif
                        </div>
                        <div class="accordion accordion-flush catalog-category-accordion" id="catalogCategoryAccordion">
                            @foreach ($rooms as $room)
                                @php
                                    $roomOpen = $filters['category'] === $room->slug
                                        || $room->children->contains(fn ($child) => $child->slug === $filters['category']);
                                    $collapseId = 'catalog-room-'.$room->id;
                                @endphp
                                <div class="accordion-item border-0 border-bottom">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button {{ $roomOpen ? '' : 'collapsed' }} px-0 py-2 shadow-none font-heading fw-bold small text-dark bg-transparent"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#{{ $collapseId }}"
                                                aria-expanded="{{ $roomOpen ? 'true' : 'false' }}"
                                                aria-controls="{{ $collapseId }}">
                                            {{ $room->name }}
                                        </button>
                                    </h2>
                                    <div id="{{ $collapseId }}" class="accordion-collapse collapse {{ $roomOpen ? 'show' : '' }}" data-bs-parent="#catalogCategoryAccordion">
                                        <div class="accordion-body px-0 pt-0 pb-3">
                                            <a href="{{ route('store.catalog', array_merge($preservedFilters, ['category' => $room->slug])) }}"
                                               class="d-block small py-1 text-decoration-none {{ $filters['category'] === $room->slug ? 'text-amber fw-bold' : 'text-muted' }}">
                                                All {{ $room->name }}
                                            </a>
                                            @forelse ($room->children as $child)
                                                <a href="{{ route('store.catalog', array_merge($preservedFilters, ['category' => $child->slug])) }}"
                                                   class="d-block small py-1 ps-2 text-decoration-none {{ $filters['category'] === $child->slug ? 'text-amber fw-bold' : 'text-dark' }}">
                                                    {{ $child->name }}
                                                </a>
                                            @empty
                                                <span class="text-muted small">No subcategories</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <form method="GET" action="{{ route('store.catalog') }}">
                        @if ($filters['category'] !== '')
                            <input type="hidden" name="category" value="{{ $filters['category'] }}">
                        @endif
                        <div class="mb-3">
                            <label class="font-heading fw-bold small text-dark mb-2 d-block">Search</label>
                            <input type="text" name="q" class="form-control shadow-none" value="{{ $filters['q'] }}" placeholder="Sofa, bed, desk...">
                        </div>
                        @if ($materials->isNotEmpty())
                            <div class="mb-4 border-top pt-3">
                                <label class="font-heading fw-bold small text-dark mb-2 d-block">Material & Build</label>
                                <select name="material" class="form-select form-select-sm shadow-none font-heading">
                                    <option value="">All Materials</option>
                                    @foreach ($materials as $material)
                                        <option value="{{ $material }}" @selected($filters['material'] === $material)>{{ $material }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="mb-4 border-top pt-3">
                            <label class="font-heading fw-bold small text-dark mb-2 d-block">Max Price (₹)</label>
                            <input type="range" class="form-range" min="5000" max="150000" step="1000" name="max_price" value="{{ $filters['max_price'] }}" id="priceRange" oninput="document.getElementById('priceLabel').textContent='₹'+Number(this.value).toLocaleString('en-IN')">
                            <div class="d-flex justify-content-between small text-muted font-heading fw-semibold mt-1">
                                <span>₹5,000</span>
                                <span class="text-amber fw-bold" id="priceLabel">₹{{ number_format((float) $filters['max_price'], 0) }}</span>
                                <span>₹1,50,000</span>
                            </div>
                        </div>
                        <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
                        <button class="btn btn-primary-luxury w-100 py-2 font-heading fw-bold small"><i class="fas fa-filter me-1"></i> Apply Filters</button>
                    </form>
                </div>
            </div>

            <div class="col-12 col-lg-9">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <span class="small font-heading fw-semibold text-muted">Showing <strong class="text-dark">{{ $products->total() }}</strong> Furniture Products</span>
                        <form method="GET" action="{{ route('store.catalog') }}" class="d-flex align-items-center gap-2">
                            @foreach (request()->except('sort', 'page') as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <span class="small font-heading fw-semibold text-muted me-1">Sort By:</span>
                            <select name="sort" class="form-select form-select-sm shadow-none font-heading fw-semibold text-dark" style="width: 200px;" onchange="this.form.submit()">
                                <option value="popular" @selected($filters['sort'] === 'popular')>Popular & Featured</option>
                                <option value="price_asc" @selected($filters['sort'] === 'price_asc')>Price: Low to High</option>
                                <option value="price_desc" @selected($filters['sort'] === 'price_desc')>Price: High to Low</option>
                                <option value="newest" @selected($filters['sort'] === 'newest')>Newest</option>
                            </select>
                        </form>
                    </div>
                </div>

                <div class="row g-4">
                    @forelse ($products as $product)
                        @include('store.partials.product-card', ['product' => $product])
                    @empty
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                                <h5 class="font-heading fw-bold">No products match these filters</h5>
                                <p class="text-muted mb-3">Try clearing the filters or choosing another room.</p>
                                <a href="{{ route('store.catalog') }}" class="btn btn-primary-luxury">Reset Filters</a>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if ($products->hasPages())
                    <div class="mt-4">{{ $products->links() }}</div>
                @endif
            </div>
        </div>
    </section>

    @include('store.partials.footer')
    @include('store.partials.whatsapp')
@endsection
