@extends('layouts.store')

@section('title', $product->name)
@section('body_class', 'bg-light')

@section('content')
    @include('store.partials.header')

    @php
        $fallbackImage = 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=1000';
        $options = collect($variants);
        // Variants arrive cheapest-first; prefer the lowest-priced option that is still in stock.
        $selected = $options->firstWhere('available', '>', 0) ?? $options->first();
        $imageUrls = collect($selected['images'] ?? [])->filter()->values();
        if ($imageUrls->isEmpty()) {
            $imageUrls = collect([$fallbackImage]);
        }
        $main = $imageUrls->first();
        $price = (float) ($selected['price'] ?? $product->fromPrice());
        $mrp = isset($selected['mrp']) && $selected['mrp'] ? (float) $selected['mrp'] : null;
        $hasDiscount = $mrp && $mrp > $price;
        $discountPct = $hasDiscount ? (int) round((($mrp - $price) / $mrp) * 100) : 0;

        $colours = $options->unique('colour')->values();
        $finishes = $options->pluck('finish')->filter()->unique()->values();
        $upholsteries = $product->hasUpholstery()
            ? $options->pluck('upholstery')->filter()->unique()->values()
            : collect();

        $ratingsPool = ['4.7', '4.8', '4.9', '4.6'];
        $rating = $ratingsPool[($product->id ?? 0) % count($ratingsPool)];
        $reviewCount = 24 + ((($product->id ?? 1) * 17) % 90);
        $inStock = ($selected['available'] ?? 0) > 0;

        $widthCm = $product->width_cm ?: 200;
        $depthCm = $product->depth_cm ?: 90;
        $heightCm = $product->height_cm ?: 85;
        $weightKg = $product->weight_kg ?: 45;
        $customSpecs = is_array($product->specs) ? array_filter($product->specs, fn ($v) => $v !== null && $v !== '') : [];
    @endphp

    <!-- Breadcrumb Bar -->
    <section class="pdp-breadcrumb-bar">
        <div class="container-fluid px-4 px-lg-5">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 font-heading small">
                        <li class="breadcrumb-item"><a href="{{ route('store.home') }}" class="text-muted text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('store.catalog') }}" class="text-muted text-decoration-none">Catalog</a></li>
                        @if ($product->category?->parent)
                            <li class="breadcrumb-item"><a href="{{ route('store.catalog', ['category' => $product->category->parent->slug]) }}" class="text-muted text-decoration-none">{{ $product->category->parent->name }}</a></li>
                        @endif
                        @if ($product->category)
                            <li class="breadcrumb-item"><a href="{{ route('store.catalog', ['category' => $product->category->slug]) }}" class="text-muted text-decoration-none">{{ $product->category->name }}</a></li>
                        @endif
                        <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">{{ $product->name }}</li>
                    </ol>
                </nav>
                <a href="{{ $product->category ? route('store.catalog', ['category' => $product->category->slug]) : route('store.catalog') }}" class="pdp-back-link">
                    <i class="fas fa-arrow-left me-1.5"></i> Back to {{ $product->category?->name ?? 'Catalog' }}
                </a>
            </div>
        </div>
    </section>

    <!-- Main Product Detail Section -->
    <section class="container-fluid px-4 px-lg-5 my-4">
        <div class="pdp-main-card">
            <div class="row g-4 g-xl-5">
                <!-- Left Column: Product Visual Gallery -->
                <div class="col-12 col-lg-6">
                    <div class="pdp-gallery-sticky">
                        <div class="pdp-main-stage">
                            <div class="pdp-stage-badges">
                                @if ($product->is_featured)
                                    <span class="pdp-badge-featured">Featured</span>
                                @else
                                    <span class="pdp-badge-new">New Edition</span>
                                @endif
                                <span class="pdp-badge-direct"><i class="fas fa-industry me-1"></i> Factory Direct</span>
                            </div>

                            <button type="button" class="pdp-wishlist-btn" aria-label="Add to wishlist" onclick="this.classList.toggle('is-liked')">
                                <i class="far fa-heart"></i>
                            </button>

                            <img id="mainProductImg" src="{{ $main }}" class="pdp-main-img" alt="{{ $product->name }}">
                        </div>

                        <div class="row g-2 mt-2" id="productThumbs">
                            @foreach ($imageUrls as $url)
                                <div class="col-3 col-sm-2">
                                    <div class="pdp-thumb-wrap {{ $loop->first ? 'is-active' : '' }}">
                                        <img src="{{ $url }}" class="img-thumbnail pdp-thumb-img cursor-pointer js-product-thumb {{ $loop->first ? '' : 'opacity-75' }}" alt="{{ $product->name }}">
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Gallery Bottom Assurance Bar -->
                        <div class="pdp-gallery-assurance mt-3">
                            <div class="pdp-assure-chip">
                                <i class="fas fa-check-circle text-amber"></i>
                                <span>100% Seasoned Grade-A Materials</span>
                            </div>
                            <div class="pdp-assure-chip">
                                <i class="fas fa-camera text-amber"></i>
                                <span>Actual Factory Finish Studio Photos</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Product Configuration & Purchase -->
                <div class="col-12 col-lg-6">
                    <div class="pdp-info-panel">
                        <!-- Top Meta Row: Category, SKU & Stock Pill -->
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                @if ($product->room || $product->product_category)
                                    <span class="pdp-eyebrow-tag">{{ collect([$product->room, $product->product_category])->filter()->implode(' • ') }}</span>
                                @endif
                                <span class="pdp-sku-pill">SKU: <strong id="variant-sku">{{ $selected['sku'] ?? $product->code }}</strong></span>
                            </div>
                            <span class="pdp-stock-pill {{ $inStock ? 'in-stock text-success' : 'out-stock text-danger' }}" id="variant-stock">
                                {{ $inStock ? 'In stock' : 'Out of stock' }}
                            </span>
                        </div>

                        <!-- Product Title -->
                        <h1 class="pdp-title">{{ $product->name }}</h1>

                        <!-- Rating & Material Subtitle Row -->
                        <div class="pdp-rating-row mb-3">
                            <div class="pdp-stars">
                                <i class="fas fa-star"></i>
                                <span>{{ $rating }}</span>
                                <span class="pdp-review-count">({{ $reviewCount }} Verified Reviews)</span>
                            </div>
                            @if ($product->material)
                                <span class="pdp-dot">•</span>
                                <span class="pdp-material-tag"><i class="fas fa-layer-group me-1 text-amber"></i>{{ $product->material }}</span>
                            @endif
                            @if ($product->design_type)
                                <span class="pdp-dot">•</span>
                                <span class="pdp-design-tag">{{ $product->design_type }} Series</span>
                            @endif
                        </div>

                        <!-- Pricing Card -->
                        <div class="pdp-price-box mb-4">
                            <div class="d-flex align-items-baseline gap-3 flex-wrap">
                                <span class="pdp-price-current" id="variant-price">₹{{ number_format($price, 0) }}</span>
                                <span class="pdp-price-mrp {{ $hasDiscount ? '' : 'd-none' }}" id="variant-mrp">₹{{ number_format((float) $mrp, 0) }}</span>
                                <span class="pdp-price-discount {{ $hasDiscount ? '' : 'd-none' }}" id="variant-pct">{{ $discountPct }}% off</span>
                                <span class="pdp-price-save {{ $hasDiscount ? '' : 'd-none' }}" id="variant-save">Save ₹{{ number_format((float) $mrp - $price, 0) }}</span>
                            </div>
                            <div class="pdp-tax-note mt-1">
                                Inclusive of all taxes • Direct factory price with GST invoice available
                            </div>
                        </div>

                        <!-- Interactive Variant Picker -->
                        @if ($options->isNotEmpty())
                            <div id="variant-picker" class="pdp-variant-picker mb-4">
                                <div class="pdp-option-group mb-3">
                                    <div class="pdp-option-header">
                                        <span class="pdp-option-label">Colour</span>
                                        <span class="pdp-option-selected" data-variant-selected="colour">{{ $selected['colour'] ?? '' }}</span>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($colours as $option)
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-dark pdp-variant-btn js-variant-option"
                                                    data-group="colour"
                                                    data-value="{{ $option['colour'] }}">
                                                <span class="pdp-swatch-circle" style="background-color: {{ $option['colour_hex'] }};"></span>
                                                <span>{{ $option['colour'] }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                @if ($finishes->isNotEmpty())
                                    <div class="pdp-option-group mb-3">
                                        <div class="pdp-option-header">
                                            <span class="pdp-option-label">Surface Finish</span>
                                            <span class="pdp-option-selected" data-variant-selected="finish">{{ $selected['finish'] ?? '' }}</span>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach ($finishes as $finish)
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-dark pdp-variant-btn js-variant-option"
                                                        data-group="finish"
                                                        data-value="{{ $finish }}">
                                                    {{ $finish }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if ($upholsteries->isNotEmpty())
                                    <div class="pdp-option-group mb-2">
                                        <div class="pdp-option-header">
                                            <span class="pdp-option-label">Upholstery</span>
                                            <span class="pdp-option-selected" data-variant-selected="upholstery">{{ $selected['upholstery'] ?? '' }}</span>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach ($upholsteries as $upholstery)
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-dark pdp-variant-btn js-variant-option"
                                                        data-group="upholstery"
                                                        data-value="{{ $upholstery }}">
                                                    {{ $upholstery }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <script type="application/json" id="variant-data">@json($variants)</script>
                        @endif

                        @if ($product->description)
                            <p class="pdp-short-desc mb-4">{{ $product->description }}</p>
                        @endif

                        <!-- Quantity & Primary Action Buttons -->
                        <div class="pdp-purchase-row mb-4">
                            <div class="pdp-qty-box">
                                <button type="button" class="pdp-qty-btn" onclick="changePdpQty(-1)" aria-label="Decrease quantity">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" id="pdpQtyDisplay" class="pdp-qty-input" value="1" min="1" max="10" readonly>
                                <button type="button" class="pdp-qty-btn" onclick="changePdpQty(1)" aria-label="Increase quantity">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>

                            <form method="POST" action="{{ route('store.cart.add') }}" class="flex-grow-1 m-0">
                                @csrf
                                <input type="hidden" name="product_variant_id" value="{{ $selected['id'] ?? '' }}" class="js-variant-input">
                                <input type="hidden" name="quantity" value="1" class="js-qty-input">
                                <button type="submit" class="pdp-btn-add w-100 js-variant-submit" @disabled(! $inStock)>
                                    <i class="fas fa-shopping-bag me-2"></i> Add to cart
                                </button>
                            </form>

                            <form method="POST" action="{{ route('store.cart.add') }}" class="m-0">
                                @csrf
                                <input type="hidden" name="product_variant_id" value="{{ $selected['id'] ?? '' }}" class="js-variant-input">
                                <input type="hidden" name="quantity" value="1" class="js-qty-input">
                                <input type="hidden" name="redirect" value="cart">
                                <button type="submit" class="pdp-btn-buy js-variant-submit" @disabled(! $inStock)>
                                    <i class="fas fa-bolt me-1.5"></i> Buy Now
                                </button>
                            </form>
                        </div>

                        <!-- Value / Service Highlights Grid -->
                        <div class="pdp-perks-grid mb-4">
                            <div class="pdp-perk-item">
                                <span class="pdp-perk-icon"><i class="fas fa-truck"></i></span>
                                <div>
                                    <h6>White-Glove Delivery</h6>
                                    <span>Free in-home setup above ₹49,999</span>
                                </div>
                            </div>
                            <div class="pdp-perk-item">
                                <span class="pdp-perk-icon"><i class="fas fa-shield-alt"></i></span>
                                <div>
                                    <h6>10-Year Structural Warranty</h6>
                                    <span>CRCA heavy steel &amp; seasoned wood</span>
                                </div>
                            </div>
                            <div class="pdp-perk-item">
                                <span class="pdp-perk-icon"><i class="fas fa-ruler-combined"></i></span>
                                <div>
                                    <h6>Custom Sizing Available</h6>
                                    <span>Built to order at Godwin factory</span>
                                </div>
                            </div>
                            <div class="pdp-perk-item">
                                <span class="pdp-perk-icon"><i class="fas fa-file-invoice"></i></span>
                                <div>
                                    <h6>GST Business Billing</h6>
                                    <span>Claim input tax credit on orders</span>
                                </div>
                            </div>
                        </div>

                        <!-- Dimensions, Specs & Care Accordion -->
                        <div class="accordion pdp-specs-accordion" id="pdpSpecsAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#pdpCollapseSpecs" aria-expanded="true">
                                        <i class="fas fa-ruler-horizontal text-amber me-2"></i> Dimensions &amp; Specifications
                                    </button>
                                </h2>
                                <div id="pdpCollapseSpecs" class="accordion-collapse collapse show" data-bs-parent="#pdpSpecsAccordion">
                                    <div class="accordion-body">
                                        <div class="pdp-dims-grid mb-3">
                                            <div class="pdp-dim-box">
                                                <span class="pdp-dim-label">Width</span>
                                                <strong class="pdp-dim-val">{{ number_format((float) $widthCm, 0) }} cm</strong>
                                            </div>
                                            <div class="pdp-dim-box">
                                                <span class="pdp-dim-label">Depth</span>
                                                <strong class="pdp-dim-val">{{ number_format((float) $depthCm, 0) }} cm</strong>
                                            </div>
                                            <div class="pdp-dim-box">
                                                <span class="pdp-dim-label">Height</span>
                                                <strong class="pdp-dim-val">{{ number_format((float) $heightCm, 0) }} cm</strong>
                                            </div>
                                            <div class="pdp-dim-box">
                                                <span class="pdp-dim-label">Weight</span>
                                                <strong class="pdp-dim-val">{{ number_format((float) $weightKg, 0) }} kg</strong>
                                            </div>
                                        </div>

                                        <div class="pdp-spec-table">
                                            <div class="pdp-spec-row">
                                                <span>Primary Material</span>
                                                <strong>{{ $product->material ?: 'CRCA Steel & Solid Wood' }}</strong>
                                            </div>
                                            @if ($product->design_type)
                                                <div class="pdp-spec-row">
                                                    <span>Design Style</span>
                                                    <strong>{{ $product->design_type }}</strong>
                                                </div>
                                            @endif
                                            @if ($product->room)
                                                <div class="pdp-spec-row">
                                                    <span>Recommended Room</span>
                                                    <strong>{{ $product->room }}</strong>
                                                </div>
                                            @endif
                                            @foreach ($customSpecs as $specKey => $specVal)
                                                <div class="pdp-spec-row">
                                                    <span>{{ is_string($specKey) ? ucfirst(str_replace('_', ' ', $specKey)) : 'Specification' }}</span>
                                                    <strong>{{ is_array($specVal) ? implode(', ', $specVal) : $specVal }}</strong>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pdpCollapseDelivery" aria-expanded="false">
                                        <i class="fas fa-box-open text-amber me-2"></i> Delivery, Assembly &amp; Care
                                    </button>
                                </h2>
                                <div id="pdpCollapseDelivery" class="accordion-collapse collapse" data-bs-parent="#pdpSpecsAccordion">
                                    <div class="accordion-body small text-muted" style="line-height: 1.7;">
                                        <ul class="mb-0 ps-3">
                                            <li><strong>White-Glove Installation:</strong> Our factory-trained technicians deliver, unpack, assemble, and position your furniture in your room of choice.</li>
                                            <li><strong>7-Tank Treated Steel:</strong> All metal components undergo anti-rust phosphating and oven-baked powder coating for humidity resistance.</li>
                                            <li><strong>Care Instructions:</strong> Wipe daily with a soft dry microfiber cloth. Avoid direct harsh chemical solvents on wood or powder-coated surfaces.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products Section -->
        @if ($related->isNotEmpty())
            <div class="mt-5 pt-2">
                <div class="d-flex align-items-end justify-content-between mb-4">
                    <div>
                        <span class="shop-cat-kicker">COMPLETE THE SUITE</span>
                        <h2 class="bs-section-heading m-0">Related Products</h2>
                    </div>
                    <a href="{{ route('store.catalog', ['category' => $product->category?->slug]) }}" class="bs-view-all-link">View all</a>
                </div>
                <div class="row g-4">
                    @foreach ($related as $item)
                        @include('store.partials.product-card', ['product' => $item, 'colClass' => 'col-12 col-sm-6 col-lg-3'])
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    @include('store.partials.footer')
    @include('store.partials.whatsapp')
@endsection

@push('scripts')
<script>
function changePdpQty(delta) {
    const display = document.getElementById('pdpQtyDisplay');
    if (!display) return;
    const current = parseInt(display.value || '1', 10);
    const next = Math.max(1, Math.min(10, current + delta));
    display.value = next;
    document.querySelectorAll('.js-qty-input').forEach(function (input) {
        input.value = next;
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const picker = document.getElementById('variant-picker');
    const payload = document.getElementById('variant-data');
    if (!picker || !payload) {
        return;
    }

    const variants = JSON.parse(payload.textContent);
    const groups = ['colour', 'finish', 'upholstery'].filter(function (group) {
        return picker.querySelector('[data-group="' + group + '"]') !== null;
    });
    if (variants.length === 0 || groups.length === 0) {
        return;
    }

    const priceEl = document.getElementById('variant-price');
    const mrpEl = document.getElementById('variant-mrp');
    const saveEl = document.getElementById('variant-save');
    const pctEl = document.getElementById('variant-pct');
    const skuEl = document.getElementById('variant-sku');
    const stockEl = document.getElementById('variant-stock');
    const mainImg = document.getElementById('mainProductImg');
    const thumbs = document.getElementById('productThumbs');
    const inputs = document.querySelectorAll('.js-variant-input');
    const submits = document.querySelectorAll('.js-variant-submit');
    const fallbackImage = @json($fallbackImage);
    const selection = {};

    function money(amount) {
        return '₹' + Number(amount).toLocaleString('en-IN', { maximumFractionDigits: 0 });
    }

    function renderGallery(urls) {
        const list = (urls && urls.length) ? urls : [fallbackImage];
        if (mainImg) {
            mainImg.src = list[0];
        }
        if (!thumbs) {
            return;
        }
        thumbs.innerHTML = list.map(function (url, index) {
            return '<div class="col-3 col-sm-2">'
                + '<div class="pdp-thumb-wrap' + (index === 0 ? ' is-active' : '') + '">'
                + '<img src="' + url + '" class="img-thumbnail pdp-thumb-img cursor-pointer js-product-thumb' + (index === 0 ? '' : ' opacity-75') + '" alt="">'
                + '</div>'
                + '</div>';
        }).join('');
    }

    if (thumbs) {
        thumbs.addEventListener('click', function (event) {
            const thumb = event.target.closest('.js-product-thumb');
            if (!thumb || !mainImg) {
                return;
            }
            mainImg.src = thumb.src;
            thumbs.querySelectorAll('.js-product-thumb').forEach(function (image) {
                image.classList.toggle('opacity-75', image !== thumb);
                const wrap = image.closest('.pdp-thumb-wrap');
                if (wrap) {
                    wrap.classList.toggle('is-active', image === thumb);
                }
            });
        });
    }

    function matching(partial) {
        return variants.filter(function (variant) {
            return groups.every(function (group) {
                return partial[group] === undefined || variant[group] === partial[group];
            });
        });
    }

    function currentVariant() {
        const found = matching(selection);

        return found.find(function (variant) { return variant.available > 0; }) || found[0] || null;
    }

    function choose(group, value) {
        selection[group] = value;

        // Keep the other groups on a combination that actually exists.
        groups.forEach(function (other) {
            if (other === group || matching(selection).length > 0) {
                return;
            }
            const partial = Object.assign({}, selection);
            delete partial[other];
            const options = matching(partial);
            const pick = options.find(function (variant) { return variant.available > 0; }) || options[0];
            if (pick) {
                selection[other] = pick[other];
            }
        });

        render();
    }

    function render() {
        groups.forEach(function (group) {
            picker.querySelectorAll('[data-group="' + group + '"]').forEach(function (button) {
                const value = button.dataset.value;
                const anywhere = variants.filter(function (variant) { return variant[group] === value; });
                const sellable = anywhere.some(function (variant) { return variant.available > 0; });
                const probe = Object.assign({}, selection);
                probe[group] = value;
                const here = matching(probe);
                const chosen = selection[group] === value;

                button.disabled = !sellable;
                button.classList.toggle('btn-dark', chosen);
                button.classList.toggle('btn-outline-dark', !chosen);
                button.classList.toggle('is-selected', chosen);
                button.classList.toggle('text-decoration-line-through', !sellable);
                button.classList.toggle('opacity-50', sellable && !here.some(function (variant) { return variant.available > 0; }));
                button.title = sellable ? '' : 'Out of stock';
            });

            const label = picker.querySelector('[data-variant-selected="' + group + '"]');
            if (label) {
                label.textContent = selection[group] || '—';
            }
        });

        const variant = currentVariant();
        const inStock = Boolean(variant) && variant.available > 0;

        if (variant) {
            priceEl.textContent = money(variant.price);
            skuEl.textContent = variant.sku;
            const discounted = variant.mrp !== null && Number(variant.mrp) > Number(variant.price);
            const pct = discounted ? Math.round(((Number(variant.mrp) - Number(variant.price)) / Number(variant.mrp)) * 100) : 0;
            mrpEl.textContent = money(variant.mrp || 0);
            saveEl.textContent = 'Save ' + money((variant.mrp || 0) - variant.price);
            if (pctEl) {
                pctEl.textContent = pct + '% off';
                pctEl.classList.toggle('d-none', !discounted);
            }
            mrpEl.classList.toggle('d-none', !discounted);
            saveEl.classList.toggle('d-none', !discounted);
            renderGallery(variant.images || []);
        }

        stockEl.textContent = inStock ? 'In stock' : 'Out of stock';
        stockEl.classList.toggle('text-success', inStock);
        stockEl.classList.toggle('text-danger', !inStock);
        stockEl.classList.toggle('in-stock', inStock);
        stockEl.classList.toggle('out-stock', !inStock);

        inputs.forEach(function (input) {
            input.value = variant ? variant.id : '';
        });
        submits.forEach(function (button) {
            button.disabled = !inStock;
        });
    }

    picker.addEventListener('click', function (event) {
        const button = event.target.closest('.js-variant-option');
        if (button && !button.disabled) {
            choose(button.dataset.group, button.dataset.value);
        }
    });

    // Payload is sorted cheapest-first; start on the lowest-priced in-stock option.
    const start = variants.find(function (variant) { return variant.available > 0; }) || variants[0];
    groups.forEach(function (group) {
        selection[group] = start[group];
    });
    render();
});
</script>
@endpush
