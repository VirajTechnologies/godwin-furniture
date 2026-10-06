@php
    $price = $product->storePrice();
    $mrp = $product->mrp();
    $images = $product->images;
    if ($images->isEmpty()) {
        $images = collect([(object) ['url' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=600']]);
    }
    $carouselId = 'productCarousel'.$product->id;
@endphp

<div class="col-12 col-md-6 col-lg-4">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
        <div class="position-relative overflow-hidden">
            @if ($product->is_featured)
                <span class="position-absolute top-0 start-0 m-3 badge bg-amber text-white font-heading px-2.5 py-1.5 fw-bold" style="z-index: 10; font-size: 10px;">Featured</span>
            @endif
            <div id="{{ $carouselId }}" class="carousel slide product-card-carousel" data-bs-interval="false">
                @if ($images->count() > 1)
                    <div class="carousel-indicators mb-2" style="z-index: 12;">
                        @foreach ($images as $index => $image)
                            <button type="button" onclick="goToProductSlide('{{ $carouselId }}', {{ $index }}, event)" class="{{ $index === 0 ? 'active' : '' }}" aria-label="Slide {{ $index + 1 }}"></button>
                        @endforeach
                    </div>
                @endif
                <div class="carousel-inner">
                    @foreach ($images as $index => $image)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <a href="{{ route('store.product', $product->slug) }}">
                                <img src="{{ $image->url }}" class="card-img-top object-fit-cover d-block w-100" style="height: 220px;" alt="{{ $product->name }}">
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
        <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
            <div>
                @if ($product->material)
                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">{{ $product->material }}</span>
                @endif
                <a href="{{ route('store.product', $product->slug) }}" class="text-decoration-none">
                    <h5 class="font-heading fw-bold text-dark fs-6 mb-3 text-truncate" title="{{ $product->name }}" style="line-height: 1.4;">{{ $product->name }}</h5>
                </a>
            </div>
            <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                <div>
                    <span class="fw-bold fs-5 text-dark">₹{{ number_format($price, 0) }}</span>
                    @if ($mrp && $mrp > $price)
                        <span class="text-muted text-decoration-line-through small ms-1">₹{{ number_format($mrp, 0) }}</span>
                    @endif
                </div>
                <form method="POST" action="{{ route('store.cart.add') }}" class="m-0">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <button type="submit" class="btn btn-sm btn-primary-luxury px-3 py-2 font-heading fw-semibold"><i class="fas fa-shopping-bag me-1"></i> Add</button>
                </form>
            </div>
        </div>
    </div>
</div>
