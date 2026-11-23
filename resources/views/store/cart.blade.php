@extends('layouts.store')

@section('title', 'Cart')
@section('body_class', 'gw-theme-body')

@section('content')
    @include('store.partials.header')

    @php
        $totalSavings = $lines->sum(function ($line) {
            return ($line->mrp && $line->mrp > $line->unit_price)
                ? ($line->mrp - $line->unit_price) * $line->quantity
                : 0;
        });
    @endphp

    <section class="gw-wrap gw-catalog-wrap">
        <nav aria-label="breadcrumb" class="gw-cat-crumbs">
            <a href="{{ route('store.home') }}">Home</a>
            <span>/</span>
            <span>Cart</span>
        </nav>

        @if (session('success'))
            <div class="gw-alert gw-alert-success mt-3">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="gw-alert gw-alert-danger mt-3">{{ session('error') }}</div>
        @endif

        @if ($lines->isEmpty())
            <h1 class="gw-cat-heading">Your cart is empty</h1>
            <p class="gw-cat-count mb-4">Browse our collections and add furniture you like.</p>
            <a href="{{ route('store.catalog') }}" class="gw-btn">Browse collections</a>
        @else
            <div class="gw-cat-topbar">
                <div>
                    <h1 class="gw-cat-heading mb-1">Cart</h1>
                    <div class="gw-cat-count">{{ $itemCount }} {{ $itemCount === 1 ? 'item' : 'items' }} in your bag</div>
                </div>
                <a href="{{ route('store.catalog') }}" class="gw-view-link">Continue shopping</a>
            </div>

            <table class="gw-cart-table">
                <tbody>
                    @foreach ($lines as $line)
                        @php
                            $product = $line->product;
                            $variant = $line->variant;
                            $image = $product->images->first()?->url
                                ?? 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=200';
                            $metaParts = array_filter([
                                $product->material,
                                $variant->label(),
                                $variant->sku,
                            ]);
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <a href="{{ route('store.product', $product->slug) }}">
                                        <img src="{{ $image }}" class="gw-cart-thumb" alt="{{ $product->name }}">
                                    </a>
                                    <div>
                                        <a href="{{ route('store.product', $product->slug) }}" class="gw-cart-name">{{ $product->name }}</a>
                                        @if ($metaParts !== [])
                                            <div class="gw-cart-meta">{{ implode(' · ', $metaParts) }}</div>
                                        @endif
                                        <div class="gw-cart-meta">
                                            ₹{{ number_format($line->unit_price, 0) }}
                                            @if ($line->mrp && $line->mrp > $line->unit_price)
                                                <s class="ms-1">₹{{ number_format($line->mrp, 0) }}</s>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td style="width: 150px;">
                                <form method="POST" action="{{ route('store.cart.update', $variant) }}" class="gw-qty-group m-0">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" name="quantity" value="{{ max(0, $line->quantity - 1) }}" class="gw-qty-btn" title="Decrease">−</button>
                                    <input type="text" class="gw-qty-val" value="{{ $line->quantity }}" readonly aria-label="Quantity">
                                    <button type="submit" name="quantity" value="{{ $line->quantity + 1 }}" class="gw-qty-btn" @disabled($line->quantity >= \App\Support\StoreCart::MAX_QUANTITY) title="Increase">+</button>
                                </form>
                            </td>

                            <td style="width: 140px; text-align: right; font-weight: 400; font-size: 1.05rem;">
                                ₹{{ number_format($line->line_total, 0) }}
                            </td>

                            <td style="width: 110px;">
                                <form method="POST" action="{{ route('store.cart.remove', $variant) }}" class="m-0 d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="gw-btn gw-btn-sm gw-btn-outline">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="gw-cart-summary">
                <div class="gw-cart-totals">
                    Subtotal <strong>₹{{ number_format($subtotal, 0) }}</strong>
                    @if ($totalSavings > 0)
                        <span class="gw-cart-save"> · You save ₹{{ number_format($totalSavings, 0) }}</span>
                    @endif
                    <span> · Delivery calculated at checkout</span>
                </div>

                <div class="d-flex flex-column align-items-sm-end gap-2">
                    <a href="{{ route('store.checkout') }}" class="gw-btn">Proceed to checkout</a>
                    @guest
                        <span class="small" style="color: var(--mut);">Sign in or register to checkout.</span>
                    @endguest
                </div>
            </div>
        @endif
    </section>

    @include('store.partials.footer')
    @include('store.partials.whatsapp')
@endsection
