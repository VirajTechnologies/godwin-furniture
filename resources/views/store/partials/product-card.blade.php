@php
    $firstVariant = $product->activeVariants->sortBy('price')->first() ?? $product->activeVariants->first();
    $price = $firstVariant ? (float) $firstVariant->storePrice() : (float) $product->fromPrice();
    $mrp = $firstVariant && $firstVariant->mrp() ? (float) $firstVariant->mrp() : null;
    $discount = ($mrp && $mrp > $price) ? (int) round((($mrp - $price) / $mrp) * 100) : null;

    $colourName = $firstVariant?->colour ?: 'Walnut';
    $colourHex = $firstVariant?->colour_hex ?: \App\Support\Catalog::colourHex($colourName);
    $subtitle = $product->material ?: ($product->category?->name ?: 'Factory direct');

    $ratingsPool = ['4.6', '4.8', '4.5', '4.7', '4.9', '4.4'];
    $rating = $ratingsPool[($product->id ?? 0) % count($ratingsPool)];

    $images = $product->galleryFor($firstVariant);
    $imageUrl = $images->first()?->url
        ?? 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=700';

    $productUrl = route('store.product', $product->slug);
    $showNewBadge = $product->is_featured || (($product->id ?? 1) % 2 === 1);
    $colClass = $colClass ?? 'col-12 col-sm-6 col-xl-4';
@endphp

<div class="{{ $colClass }}">
    <div class="gw-card h-100">
        @if ($showNewBadge)
            <span class="gw-tag">New</span>
        @endif

        <button type="button"
                class="gw-hb"
                aria-label="Add {{ $product->name }} to wishlist"
                onclick="this.textContent = this.textContent.trim() === '♥' ? '♡' : '♥'">♡</button>

        <a href="{{ $productUrl }}" class="gw-card-media">
            <img src="{{ $imageUrl }}" alt="{{ $product->name }}" loading="lazy">
        </a>

        <div class="gw-card-in">
            <div class="gw-card-nm">
                <a href="{{ $productUrl }}" title="{{ $product->name }}">{{ $product->name }}</a>
            </div>

            <div class="gw-card-pr">
                ₹{{ number_format($price, 0) }}
                @if ($mrp && $mrp > $price)
                    <s>₹{{ number_format($mrp, 0) }}</s>
                @endif
                @if ($discount)
                    <span class="gw-off">{{ $discount }}% off</span>
                @endif
            </div>

            <div class="gw-card-meta">
                ★ {{ $rating }} · <span class="gw-sw" style="background: {{ $colourHex }};"></span>{{ $colourName }} · {{ $subtitle }}
            </div>

            <div class="gw-card-actions">
                @if ($firstVariant)
                    <form method="POST" action="{{ route('store.cart.add') }}" class="d-inline m-0">
                        @csrf
                        <input type="hidden" name="product_variant_id" value="{{ $firstVariant->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="gw-btn gw-btn-sm">Add to cart</button>
                    </form>
                @else
                    <a href="{{ $productUrl }}" class="gw-btn gw-btn-sm">Add to cart</a>
                @endif
                <a class="gw-btn gw-btn-sm gw-btn-outline" href="{{ $productUrl }}">Quick view</a>
            </div>
        </div>
    </div>
</div>
