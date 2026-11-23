@extends('layouts.store')

@section('title', $product->name)
@section('body_class', 'gw-theme-body')

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

        $showVariantPicker = $colours->count() > 1 || $finishes->count() > 1 || $upholsteries->count() > 1;

        $ratingsPool = ['4.6', '4.7', '4.8', '4.9'];
        $rating = $ratingsPool[($product->id ?? 0) % count($ratingsPool)];
        $inStock = ($selected['available'] ?? 0) > 0;

        $dims = collect([
            $product->width_cm ? number_format((float) $product->width_cm, 0).'W' : null,
            $product->depth_cm ? number_format((float) $product->depth_cm, 0).'D' : null,
            $product->height_cm ? number_format((float) $product->height_cm, 0).'H' : null,
        ])->filter()->implode(' × ');

        $customSpecs = is_array($product->specs) ? array_filter($product->specs, fn ($v) => $v !== null && $v !== '') : [];
        $customWhatsApp = 'https://wa.me/917418759171?text='.rawurlencode('Hi GODWIN, I would like to request customization for '.$product->name.' (SKU: '.($selected['sku'] ?? $product->code).')');
    @endphp

    <section class="gw-wrap gw-pdp-wrap">
        <nav aria-label="breadcrumb" class="gw-cat-crumbs mb-4">
            <a href="{{ route('store.home') }}">Home</a>
            <span>/</span>
            @if ($product->category?->parent)
                <a href="{{ route('store.catalog', ['category' => $product->category->parent->slug]) }}">
                    {{ strcasecmp($product->category->parent->name, 'Dining Room') === 0 ? 'Dining' : $product->category->parent->name }}
                </a>
                <span>/</span>
            @endif
            @if ($product->category)
                <a href="{{ route('store.catalog', ['category' => $product->category->slug]) }}">
                    {{ strcasecmp($product->category->name, 'Dining Room') === 0 ? 'Dining' : $product->category->name }}
                </a>
                <span>/</span>
            @endif
            <span>{{ $product->name }}</span>
        </nav>

        <div class="gw-pdp-grid">
            {{-- Left Column: Product Gallery --}}
            <div>
                <div class="gw-pdp-stage">
                    <img id="mainProductImg" src="{{ $main }}" alt="{{ $product->name }}">
                </div>

                @if ($imageUrls->count() > 1)
                    <div class="gw-pdp-thumbs" id="productThumbs">
                        @foreach ($imageUrls as $url)
                            <button type="button" class="gw-pdp-thumb js-product-thumb {{ $loop->first ? 'is-active' : '' }}" data-src="{{ $url }}" aria-label="View image {{ $loop->iteration }}">
                                <img src="{{ $url }}" alt="{{ $product->name }}">
                            </button>
                        @endforeach
                    </div>
                @else
                    <div class="gw-pdp-thumbs d-none" id="productThumbs"></div>
                @endif
            </div>

            {{-- Right Column: Details, Specs, Quantity & Actions --}}
            <div>
                <p class="gw-pdp-sku-line">
                    SKU <span id="variant-sku">{{ $selected['sku'] ?? $product->code }}</span>
                    @if ($product->category)
                        · {{ $product->category->name }}
                    @endif
                </p>

                <h1 class="gw-pdp-title">{{ $product->name }}</h1>

                <div class="gw-pdp-price">
                    <span id="variant-price">₹{{ number_format($price, 0) }}</span>
                    <s id="variant-mrp" class="{{ $hasDiscount ? '' : 'd-none' }}">₹{{ number_format((float) $mrp, 0) }}</s>
                    <span id="variant-pct" class="gw-off {{ $hasDiscount ? '' : 'd-none' }}">{{ $discountPct }}% off</span>
                </div>

                <div class="gw-pdp-rating">
                    <span class="gw-star">★</span> {{ $rating }} ·
                    <span id="variant-stock">{{ $inStock ? 'In Stock' : 'Out of Stock' }}</span>
                </div>

                @if ($options->isNotEmpty())
                    <div id="variant-picker" class="gw-pdp-variants {{ $showVariantPicker ? '' : 'd-none' }}">
                        @if ($colours->count() > 1)
                            <div>
                                <div class="gw-pdp-var-label">
                                    Colour: <strong data-variant-selected="colour">{{ $selected['colour'] ?? '' }}</strong>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($colours as $option)
                                        <button type="button"
                                                class="gw-pdp-opt-btn js-variant-option"
                                                data-group="colour"
                                                data-value="{{ $option['colour'] }}">
                                            <span class="gw-sw" style="background: {{ $option['colour_hex'] }}; margin: 0;"></span>
                                            <span>{{ $option['colour'] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($finishes->count() > 1)
                            <div>
                                <div class="gw-pdp-var-label">
                                    Metal finish: <strong data-variant-selected="finish">{{ $selected['finish'] ?? '' }}</strong>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($finishes as $finish)
                                        <button type="button"
                                                class="gw-pdp-opt-btn js-variant-option"
                                                data-group="finish"
                                                data-value="{{ $finish }}">
                                            {{ $finish }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($upholsteries->count() > 1)
                            <div>
                                <div class="gw-pdp-var-label">
                                    Upholstery: <strong data-variant-selected="upholstery">{{ $selected['upholstery'] ?? '' }}</strong>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($upholsteries as $upholstery)
                                        <button type="button"
                                                class="gw-pdp-opt-btn js-variant-option"
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

                {{-- Specification Table (Exact Reference Style) --}}
                <table class="gw-pdp-specs">
                    <tbody>
                        <tr>
                            <th>Material</th>
                            <td>{{ $product->material ?: 'Metal + Upholstery' }}</td>
                        </tr>
                        @if ($selected['finish'] ?? false)
                            <tr>
                                <th>Metal finish</th>
                                <td id="spec-finish">{{ $selected['finish'] }}</td>
                            </tr>
                        @endif
                        @if ($selected['colour'] ?? false)
                            <tr>
                                <th>Colour</th>
                                <td id="spec-colour">{{ $selected['colour'] }}</td>
                            </tr>
                        @endif
                        @if ($selected['upholstery'] ?? false)
                            <tr>
                                <th>Upholstery</th>
                                <td id="spec-upholstery">{{ $selected['upholstery'] }}</td>
                            </tr>
                        @endif
                        @if ($product->design_type)
                            <tr>
                                <th>Style</th>
                                <td>{{ $product->design_type }}</td>
                            </tr>
                        @endif
                        @if ($dims !== '')
                            <tr>
                                <th>Dimensions</th>
                                <td>{{ $dims }} cm</td>
                            </tr>
                        @endif
                        @foreach ($customSpecs as $specKey => $specVal)
                            <tr>
                                <th>{{ is_string($specKey) ? ucfirst(str_replace('_', ' ', $specKey)) : 'Specification' }}</th>
                                <td>{{ is_array($specVal) ? implode(', ', $specVal) : $specVal }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <th>Warranty</th>
                            <td>10-Year Structural Warranty</td>
                        </tr>
                    </tbody>
                </table>

                {{-- Quantity & Pincode Row --}}
                <div class="gw-pdp-qty-pin">
                    <label for="pdpQtyDisplay">Quantity</label>
                    <input type="number"
                           id="pdpQtyDisplay"
                           class="gw-pdp-qty-input"
                           value="1"
                           min="1"
                           max="10"
                           oninput="syncPdpQty(this.value)">

                    <label for="pdpPincode">Pincode</label>
                    <input type="text"
                           id="pdpPincode"
                           class="gw-pdp-pin-input"
                           placeholder="520001"
                           maxlength="6"
                           inputmode="numeric">

                    <button type="button" class="gw-pdp-sub-btn" onclick="checkPdpDelivery()">Check delivery</button>
                </div>
                <div id="pdpPinMsg" class="gw-pdp-pin-msg d-none"></div>

                {{-- Action Buttons Row --}}
                <div class="gw-pdp-actions">
                    <form method="POST" action="{{ route('store.cart.add') }}" class="m-0">
                        @csrf
                        <input type="hidden" name="product_variant_id" value="{{ $selected['id'] ?? '' }}" class="js-variant-input">
                        <input type="hidden" name="quantity" value="1" class="js-qty-input">
                        <button type="submit" class="gw-btn js-variant-submit" @disabled(! $inStock)>Add to cart</button>
                    </form>

                    <form method="POST" action="{{ route('store.cart.add') }}" class="m-0">
                        @csrf
                        <input type="hidden" name="product_variant_id" value="{{ $selected['id'] ?? '' }}" class="js-variant-input">
                        <input type="hidden" name="quantity" value="1" class="js-qty-input">
                        <input type="hidden" name="redirect" value="cart">
                        <button type="submit" class="gw-btn gw-btn-outline js-variant-submit" @disabled(! $inStock)>Buy now</button>
                    </form>

                    <button type="button"
                            class="gw-pdp-sub-btn"
                            onclick="this.textContent = this.textContent.trim() === 'Wishlist' ? 'Saved ♥' : 'Wishlist'">
                        Wishlist
                    </button>

                    <a href="{{ $customWhatsApp }}" target="_blank" rel="noopener" class="gw-pdp-sub-btn">
                        Request customization
                    </a>
                </div>

                @if ($product->description)
                    <p class="gw-pdp-desc">{{ $product->description }}</p>
                @endif
            </div>
        </div>

        {{-- Related Products Section --}}
        @if ($related->isNotEmpty())
            <div class="mt-5 pt-4" style="border-top: 1px solid var(--stone);">
                <div class="gw-bar">
                    <h2 class="m-0">Related products</h2>
                    <a href="{{ route('store.catalog', ['category' => $product->category?->slug]) }}" class="gw-view-link">View all</a>
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
function syncPdpQty(val) {
    var qty = Math.max(1, Math.min(10, parseInt(val || '1', 10) || 1));
    document.querySelectorAll('.js-qty-input').forEach(function (input) {
        input.value = qty;
    });
}

function checkPdpDelivery() {
    var input = document.getElementById('pdpPincode');
    var msg = document.getElementById('pdpPinMsg');
    if (!input || !msg) return;
    var pin = input.value.trim();
    msg.classList.remove('d-none');
    if (!/^\d{6}$/.test(pin)) {
        msg.textContent = 'Please enter a valid 6-digit pincode.';
        msg.style.color = '#b43c2f';
        return;
    }
    msg.textContent = 'Delivery available to ' + pin + ' · Estimated dispatch in 5–7 business days.';
    msg.style.color = '#2f6f4e';
}

document.addEventListener('DOMContentLoaded', function () {
    const picker = document.getElementById('variant-picker');
    const payload = document.getElementById('variant-data');
    const mainImg = document.getElementById('mainProductImg');
    const thumbs = document.getElementById('productThumbs');
    const fallbackImage = @json($fallbackImage);

    function bindThumbClicks() {
        if (!thumbs) return;
        thumbs.addEventListener('click', function (event) {
            const thumb = event.target.closest('.js-product-thumb');
            if (!thumb || !mainImg) return;
            mainImg.src = thumb.dataset.src || thumb.querySelector('img')?.src || mainImg.src;
            thumbs.querySelectorAll('.js-product-thumb').forEach(function (btn) {
                btn.classList.toggle('is-active', btn === thumb);
            });
        });
    }
    bindThumbClicks();

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
    const pctEl = document.getElementById('variant-pct');
    const skuEl = document.getElementById('variant-sku');
    const stockEl = document.getElementById('variant-stock');
    const specFinish = document.getElementById('spec-finish');
    const specColour = document.getElementById('spec-colour');
    const specUpholstery = document.getElementById('spec-upholstery');
    const inputs = document.querySelectorAll('.js-variant-input');
    const submits = document.querySelectorAll('.js-variant-submit');
    const selection = {};

    function money(amount) {
        return '₹' + Number(amount).toLocaleString('en-IN', { maximumFractionDigits: 0 });
    }

    function renderGallery(urls) {
        const list = (urls && urls.length) ? urls : [fallbackImage];
        if (mainImg) {
            mainImg.src = list[0];
        }
        if (!thumbs) return;
        if (list.length <= 1) {
            thumbs.classList.add('d-none');
            thumbs.innerHTML = '';
            return;
        }
        thumbs.classList.remove('d-none');
        thumbs.innerHTML = list.map(function (url, index) {
            return '<button type="button" class="gw-pdp-thumb js-product-thumb' + (index === 0 ? ' is-active' : '') + '" data-src="' + url + '">'
                + '<img src="' + url + '" alt="">'
                + '</button>';
        }).join('');
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
        groups.forEach(function (other) {
            if (other === group || matching(selection).length > 0) return;
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
                button.classList.toggle('is-selected', chosen);
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
            if (priceEl) priceEl.textContent = money(variant.price);
            if (skuEl) skuEl.textContent = variant.sku;
            const discounted = variant.mrp !== null && Number(variant.mrp) > Number(variant.price);
            const pct = discounted ? Math.round(((Number(variant.mrp) - Number(variant.price)) / Number(variant.mrp)) * 100) : 0;
            if (mrpEl) {
                mrpEl.textContent = money(variant.mrp || 0);
                mrpEl.classList.toggle('d-none', !discounted);
            }
            if (pctEl) {
                pctEl.textContent = pct + '% off';
                pctEl.classList.toggle('d-none', !discounted);
            }
            if (specFinish && variant.finish) specFinish.textContent = variant.finish;
            if (specColour && variant.colour) specColour.textContent = variant.colour;
            if (specUpholstery && variant.upholstery) specUpholstery.textContent = variant.upholstery;
            renderGallery(variant.images || []);
        }

        if (stockEl) {
            stockEl.textContent = inStock ? 'In Stock' : 'Out of Stock';
        }

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

    const start = variants.find(function (variant) { return variant.available > 0; }) || variants[0];
    groups.forEach(function (group) {
        selection[group] = start[group];
    });
    render();
});
</script>
@endpush
