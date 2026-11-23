@php
    $firstVariant = $product->activeVariants->sortBy('price')->first() ?? $product->activeVariants->first();
    $price = $firstVariant ? (float) $firstVariant->storePrice() : (float) $product->fromPrice();
    $mrp = $firstVariant && $firstVariant->mrp() ? (float) $firstVariant->mrp() : null;
    $discount = ($mrp && $mrp > $price) ? (int) round((($mrp - $price) / $mrp) * 100) : null;

    $colourName = $firstVariant?->colour ?: 'Walnut';
    $colourHex = $firstVariant?->colour_hex ?: \App\Support\Catalog::colourHex($colourName);
    $subtitle = $product->material ?: ($product->product_category ?: 'Factory direct');

    $ratingsPool = ['4.6', '4.8', '4.5', '4.7', '4.9', '4.4'];
    $rating = $ratingsPool[($product->id ?? 0) % count($ratingsPool)];

    $images = $product->galleryFor($firstVariant);
    if ($images->isEmpty()) {
        $images = collect([(object) ['url' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=700']]);
    }
    $carouselId = 'productCarousel'.$product->id;
    $productUrl = route('store.product', $product->slug);
    $showNewBadge = $product->is_featured || (($product->id ?? 1) % 2 === 1);
    $colClass = $colClass ?? 'col-12 col-md-6 col-lg-4';
@endphp

<div class="{{ $colClass }}">
    <div class="bs-product-card h-100">
        <div class="bs-card-media">
            @if ($showNewBadge)
                <span class="bs-badge-new">New</span>
            @endif

            <button type="button" class="bs-btn-wishlist" aria-label="Add {{ $product->name }} to wishlist" onclick="this.classList.toggle('is-liked')">
                <i class="far fa-heart"></i>
            </button>

            <div id="{{ $carouselId }}" class="carousel slide product-card-carousel h-100" data-bs-interval="false">
                @if ($images->count() > 1)
                    <div class="carousel-indicators mb-2" style="z-index: 12;">
                        @foreach ($images as $index => $image)
                            <button type="button" onclick="goToProductSlide('{{ $carouselId }}', {{ $index }}, event)" class="{{ $index === 0 ? 'active' : '' }}" aria-label="Slide {{ $index + 1 }}"></button>
                        @endforeach
                    </div>
                @endif
                <div class="carousel-inner h-100">
                    @foreach ($images as $index => $image)
                        <div class="carousel-item h-100 {{ $index === 0 ? 'active' : '' }}">
                            <a href="{{ $productUrl }}" class="d-block w-100 h-100">
                                <img src="{{ $image->url }}" class="bs-card-img" alt="{{ $product->name }}" loading="lazy">
                            </a>
                        </div>
                    @endforeach
                </div>
                @if ($images->count() > 1)
                    <button class="carousel-control-prev product-carousel-btn" type="button" onclick="slideProductCarousel('{{ $carouselId }}', 'prev', event)">
                        <span class="carousel-control-prev-icon-custom"><i class="fas fa-chevron-left"></i></span>
                    </button>
                    <button class="carousel-control-next product-carousel-btn" type="button" onclick="slideProductCarousel('{{ $carouselId }}', 'next', event)">
                        <span class="carousel-control-next-icon-custom"><i class="fas fa-chevron-right"></i></span>
                    </button>
                @endif
            </div>
        </div>

        <div class="bs-card-body">
            <a href="{{ $productUrl }}" class="text-decoration-none">
                <h5 class="bs-card-title" title="{{ $product->name }}">{{ $product->name }}</h5>
            </a>

            <div class="bs-price-row">
                <span class="bs-price-current">₹{{ number_format($price, 0) }}</span>
                @if ($mrp && $mrp > $price)
                    <span class="bs-price-mrp">₹{{ number_format($mrp, 0) }}</span>
                @endif
                @if ($discount)
                    <span class="bs-price-off">{{ $discount }}% off</span>
                @endif
            </div>

            <div class="bs-meta-row">
                <span class="bs-rating"><i class="fas fa-star me-1"></i>{{ $rating }}</span>
                <span class="bs-meta-dot">·</span>
                <span class="bs-swatch-dot" style="background-color: {{ $colourHex }};"></span>
                <span class="bs-colour-name">{{ $colourName }}</span>
                <span class="bs-meta-dot">·</span>
                <span class="bs-meta-sub text-truncate">{{ $subtitle }}</span>
            </div>

            <div class="bs-card-actions">
                <a href="{{ $productUrl }}" class="bs-btn-add flex-grow-1">
                    <i class="fas fa-shopping-bag me-1"></i> Choose options
                </a>
                <a href="{{ $productUrl }}" class="bs-btn-quick">Quick view</a>
            </div>
        </div>
    </div>
</div>
