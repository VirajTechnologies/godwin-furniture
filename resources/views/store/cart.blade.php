@extends('layouts.store')

@section('title', 'Shopping Bag')
@section('body_class', 'bg-light')

@section('content')
    @include('store.partials.header')

    <section class="bg-white border-bottom py-3">
        <div class="container-fluid px-4 px-lg-5">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                <div>
                    <h2 class="font-heading fw-bold text-dark m-0">Your Shopping Bag</h2>
                    <p class="text-muted small m-0">Review items, then continue to checkout.</p>
                </div>
                <a href="{{ route('store.catalog') }}" class="text-amber font-heading fw-semibold text-decoration-none small">
                    <i class="fas fa-arrow-left me-1"></i> Continue Shopping
                </a>
            </div>
        </div>
    </section>

    <section class="container-fluid px-4 px-lg-5 my-4">
        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-3 font-heading">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm rounded-3 font-heading">{{ session('error') }}</div>
        @endif

        <div class="row g-4">
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-3">
                        <h5 class="font-heading fw-bold text-dark m-0">
                            <i class="fas fa-shopping-bag text-amber me-2"></i>
                            Your Shopping Bag ({{ $itemCount }} {{ $itemCount === 1 ? 'Item' : 'Items' }})
                        </h5>
                    </div>

                    @forelse ($lines as $line)
                        @php
                            $product = $line->product;
                            $image = $product->images->first()?->url
                                ?? 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=200';
                        @endphp
                        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 {{ ! $loop->last ? 'pb-3 border-bottom mb-3' : '' }}">
                            <div class="d-flex align-items-center gap-3">
                                <a href="{{ route('store.product', $product->slug) }}">
                                    <img src="{{ $image }}" class="rounded-3 object-fit-cover" style="width: 90px; height: 90px;" alt="{{ $product->name }}">
                                </a>
                                <div>
                                    @if ($product->material)
                                        <span class="badge bg-light text-muted border font-heading px-2 py-0.5 mb-1" style="font-size: 10px;">{{ $product->material }}</span>
                                    @endif
                                    <h6 class="font-heading fw-bold text-dark m-0" style="font-size: 15px;">
                                        <a href="{{ route('store.product', $product->slug) }}" class="text-decoration-none text-dark">{{ $product->name }}</a>
                                    </h6>
                                    <span class="small text-muted font-heading">{{ $product->code }}</span>
                                    <div class="mt-1 fw-bold text-dark font-heading">
                                        ₹{{ number_format($line->unit_price, 0) }}
                                        @if ($line->mrp && $line->mrp > $line->unit_price)
                                            <span class="text-muted text-decoration-line-through small fw-normal">₹{{ number_format($line->mrp, 0) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between justify-content-sm-end gap-3 gap-sm-4">
                                <form method="POST" action="{{ route('store.cart.update', $product) }}" class="d-flex align-items-center gap-1">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" name="quantity" value="{{ max(0, $line->quantity - 1) }}" class="btn btn-outline-secondary btn-sm px-2" title="Decrease">-</button>
                                    <input type="text" class="form-control form-control-sm text-center font-heading fw-bold shadow-none" style="width: 48px;" value="{{ $line->quantity }}" readonly>
                                    <button type="submit" name="quantity" value="{{ $line->quantity + 1 }}" class="btn btn-outline-secondary btn-sm px-2" @disabled($line->quantity >= \App\Support\StoreCart::MAX_QUANTITY) title="Increase">+</button>
                                </form>
                                <span class="fw-bold font-heading text-dark fs-6" style="min-width: 90px; text-align: right;">₹{{ number_format($line->line_total, 0) }}</span>
                                <form method="POST" action="{{ route('store.cart.remove', $product) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link text-danger p-0 border-0 fs-5" title="Remove Item"><i class="far fa-trash-alt"></i></button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <h5 class="font-heading fw-bold mb-2">Your bag is empty</h5>
                            <p class="text-muted mb-3">Browse the catalog and add furniture you like.</p>
                            <a href="{{ route('store.catalog') }}" class="btn btn-primary-luxury font-heading fw-bold">Browse Catalog</a>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 80px;">
                    <h5 class="font-heading fw-bold text-dark pb-3 border-bottom mb-3"><i class="fas fa-receipt text-amber me-2"></i> Order Summary</h5>

                    <div class="d-flex justify-content-between font-heading small mb-2 text-muted">
                        <span>Bag Subtotal ({{ $itemCount }} {{ $itemCount === 1 ? 'Item' : 'Items' }})</span>
                        <span class="fw-bold text-dark">₹{{ number_format($subtotal, 2) }}</span>
                    </div>

                    <div class="d-flex justify-content-between font-heading small mb-3 text-muted">
                        <span>Delivery & Installation</span>
                        <span class="badge bg-success text-white">Calculated at checkout</span>
                    </div>

                    <div class="border-top pt-3 mb-4 d-flex justify-content-between align-items-center">
                        <span class="font-heading fw-bold text-dark fs-6">Grand Total:</span>
                        <span class="font-heading fw-bold fs-4 text-dark">₹{{ number_format($subtotal, 2) }}</span>
                    </div>

                    @if ($lines->isNotEmpty())
                        <a href="{{ route('store.checkout') }}" class="btn btn-primary-luxury w-100 py-3 font-heading fw-bold fs-6 shadow-sm">
                            <i class="fas fa-lock me-2"></i> Proceed to Checkout
                        </a>
                        <p class="text-center mt-3 small text-muted font-heading mb-0">
                            @auth
                                Continue to delivery details and place your order.
                            @else
                                Sign in or register to checkout.
                            @endauth
                        </p>
                    @else
                        <button type="button" class="btn btn-primary-luxury w-100 py-3 font-heading fw-bold fs-6 shadow-sm" disabled>
                            <i class="fas fa-lock me-2"></i> Proceed to Checkout
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @include('store.partials.footer')
    @include('store.partials.whatsapp')
@endsection
