@extends('layouts.store')

@section('title', $product->name)
@section('body_class', 'bg-light')

@section('content')
    @include('store.partials.header')

    @php
        $fallbackImage = 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=800';
        $options = collect($variants);
        // Variants arrive cheapest-first; prefer the lowest-priced option that is still in stock.
        $selected = $options->firstWhere('available', '>', 0) ?? $options->first();
        $imageUrls = collect($selected['images'] ?? [])->filter()->values();
        if ($imageUrls->isEmpty()) {
            $imageUrls = collect([$fallbackImage]);
        }
        $main = $imageUrls->first();
        $price = $selected['price'] ?? $product->fromPrice();
        $mrp = $selected['mrp'] ?? null;
        $colours = $options->unique('colour')->values();
        $finishes = $options->pluck('finish')->filter()->unique()->values();
        $upholsteries = $product->hasUpholstery()
            ? $options->pluck('upholstery')->filter()->unique()->values()
            : collect();
    @endphp

    <section class="bg-white border-bottom py-3">
        <div class="container-fluid px-4 px-lg-5">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb m-0 font-heading small">
                    <li class="breadcrumb-item"><a href="{{ route('store.home') }}" class="text-muted text-decoration-none">Home</a></li>
                    @if ($product->category?->parent)
                        <li class="breadcrumb-item"><a href="{{ route('store.catalog', ['category' => $product->category->parent->slug]) }}" class="text-muted text-decoration-none">{{ $product->category->parent->name }}</a></li>
                    @endif
                    @if ($product->category)
                        <li class="breadcrumb-item"><a href="{{ route('store.catalog', ['category' => $product->category->slug]) }}" class="text-muted text-decoration-none">{{ $product->category->name }}</a></li>
                    @endif
                    <li class="breadcrumb-item active text-amber fw-bold" aria-current="page">{{ $product->name }}</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="container-fluid px-4 px-lg-5 my-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="row g-4">
                <div class="col-12 col-lg-6">
                    <img id="mainProductImg" src="{{ $main }}" class="img-fluid rounded-4 w-100 object-fit-cover mb-3" style="max-height: 480px;" alt="{{ $product->name }}">
                    <div class="row g-2" id="productThumbs">
                        @foreach ($imageUrls as $url)
                            <div class="col-3">
                                <img src="{{ $url }}" class="img-thumbnail rounded-3 cursor-pointer js-product-thumb {{ $loop->first ? '' : 'opacity-75' }}" alt="{{ $product->name }}">
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-light text-dark border font-heading px-2.5 py-1">SKU: <span id="variant-sku">{{ $selected['sku'] ?? $product->code }}</span></span>
                        @if ($product->is_featured)
                            <span class="badge bg-amber-light text-amber font-heading px-2.5 py-1 fw-bold">Featured</span>
                        @endif
                    </div>
                    <h2 class="font-heading fw-bold text-dark mb-2" style="font-size: 26px; line-height: 1.3;">{{ $product->name }}</h2>
                    @if ($product->material)
                        <p class="text-muted font-heading small mb-3">{{ $product->material }}</p>
                    @endif
                    <div class="p-3 bg-light rounded-4 border mb-4">
                        <div class="d-flex align-items-baseline gap-3 flex-wrap">
                            <span class="fs-2 fw-bold text-dark font-heading" id="variant-price">₹{{ number_format($price, 0) }}</span>
                            <span class="fs-5 text-muted text-decoration-line-through font-heading {{ $mrp && $mrp > $price ? '' : 'd-none' }}" id="variant-mrp">₹{{ number_format((float) $mrp, 0) }}</span>
                            <span class="badge bg-success text-white font-heading px-2.5 py-1 fw-bold {{ $mrp && $mrp > $price ? '' : 'd-none' }}" id="variant-save">Save ₹{{ number_format((float) $mrp - $price, 0) }}</span>
                        </div>
                    </div>

                    @if ($options->isNotEmpty())
                        <div id="variant-picker" class="mb-4">
                            <div class="mb-3">
                                <div class="small text-muted font-heading mb-2">Colour: <span class="fw-semibold text-dark" data-variant-selected="colour">{{ $selected['colour'] ?? '' }}</span></div>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($colours as $option)
                                        <button type="button" class="btn btn-sm btn-outline-dark font-heading js-variant-option" data-group="colour" data-value="{{ $option['colour'] }}">
                                            <span class="d-inline-block rounded-circle border align-middle me-1" style="width: 14px; height: 14px; background: {{ $option['colour_hex'] }};"></span>{{ $option['colour'] }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            @if ($finishes->isNotEmpty())
                                <div class="mb-3">
                                    <div class="small text-muted font-heading mb-2">Finish: <span class="fw-semibold text-dark" data-variant-selected="finish">{{ $selected['finish'] ?? '' }}</span></div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($finishes as $finish)
                                            <button type="button" class="btn btn-sm btn-outline-dark font-heading js-variant-option" data-group="finish" data-value="{{ $finish }}">{{ $finish }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            @if ($upholsteries->isNotEmpty())
                                <div class="mb-3">
                                    <div class="small text-muted font-heading mb-2">Upholstery: <span class="fw-semibold text-dark" data-variant-selected="upholstery">{{ $selected['upholstery'] ?? '' }}</span></div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($upholsteries as $upholstery)
                                            <button type="button" class="btn btn-sm btn-outline-dark font-heading js-variant-option" data-group="upholstery" data-value="{{ $upholstery }}">{{ $upholstery }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            <div class="small font-heading {{ ($selected['available'] ?? 0) > 0 ? 'text-success' : 'text-danger' }}" id="variant-stock">
                                {{ ($selected['available'] ?? 0) > 0 ? 'In stock' : 'Out of stock' }}
                            </div>
                        </div>
                        <script type="application/json" id="variant-data">@json($variants)</script>
                    @endif

                    @if ($product->description)
                        <p class="font-heading text-dark mb-4">{{ $product->description }}</p>
                    @endif
                    <div class="d-flex flex-column flex-sm-row gap-3 mb-4">
                        <form method="POST" action="{{ route('store.cart.add') }}" class="flex-grow-1 m-0">
                            @csrf
                            <input type="hidden" name="product_variant_id" value="{{ $selected['id'] ?? '' }}" class="js-variant-input">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn btn-primary-luxury btn-lg py-3 px-4 w-100 font-heading fw-bold shadow-sm js-variant-submit" @disabled(($selected['available'] ?? 0) < 1)>
                                <i class="fas fa-shopping-bag me-2"></i> Add To Bag
                            </button>
                        </form>
                        <form method="POST" action="{{ route('store.cart.add') }}" class="m-0">
                            @csrf
                            <input type="hidden" name="product_variant_id" value="{{ $selected['id'] ?? '' }}" class="js-variant-input">
                            <input type="hidden" name="quantity" value="1">
                            <input type="hidden" name="redirect" value="cart">
                            <button type="submit" class="btn btn-dark btn-lg py-3 px-4 font-heading fw-bold js-variant-submit" @disabled(($selected['available'] ?? 0) < 1)>
                                <i class="fas fa-bolt me-2 text-warning"></i> Buy Now
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if ($related->isNotEmpty())
            <div class="mt-5">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="font-heading fw-bold text-dark m-0">Related Products</h4>
                    <a href="{{ route('store.catalog', ['category' => $product->category?->slug]) }}" class="btn btn-outline-dark font-heading fw-semibold rounded-pill px-4">View All</a>
                </div>
                <div class="row g-4">
                    @foreach ($related as $item)
                        @include('store.partials.product-card', ['product' => $item])
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
            return '<div class="col-3">'
                + '<img src="' + url + '" class="img-thumbnail rounded-3 cursor-pointer js-product-thumb' + (index === 0 ? '' : ' opacity-75') + '" alt="">'
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
            mrpEl.textContent = money(variant.mrp || 0);
            saveEl.textContent = 'Save ' + money((variant.mrp || 0) - variant.price);
            mrpEl.classList.toggle('d-none', !discounted);
            saveEl.classList.toggle('d-none', !discounted);
            renderGallery(variant.images || []);
        }

        stockEl.textContent = inStock ? 'In stock' : 'Out of stock';
        stockEl.classList.toggle('text-success', inStock);
        stockEl.classList.toggle('text-danger', !inStock);

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
