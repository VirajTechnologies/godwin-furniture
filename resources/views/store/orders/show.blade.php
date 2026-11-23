@extends('layouts.store')

@section('title', 'Order '.$order->code)
@section('body_class', 'gw-theme-body')

@section('content')
    @include('store.partials.header')

    @unless ($justPlaced)
        <section class="gw-page-head">
            <div class="gw-page-head-inner">
                <nav aria-label="breadcrumb">
                    <ol class="gw-breadcrumb">
                        <li><a href="{{ route('store.home') }}">Home</a></li>
                        <li class="gw-bc-sep">/</li>
                        <li><a href="{{ route('store.orders.index') }}">My Orders</a></li>
                        <li class="gw-bc-sep">/</li>
                        <li class="is-current" aria-current="page">{{ $order->code }}</li>
                    </ol>
                </nav>
                <div class="d-flex flex-column flex-sm-row align-items-sm-end justify-content-between gap-2">
                    <div>
                        <h1 class="gw-page-title">Order {{ $order->code }}</h1>
                        <p class="gw-page-sub">Placed {{ $order->created_at?->format('d M Y, h:i A') }}</p>
                    </div>
                    <a href="{{ route('store.orders.index') }}" class="gw-view-link">
                        <i class="fas fa-arrow-left me-1"></i> All Orders
                    </a>
                </div>
            </div>
        </section>
    @endunless

    <section class="gw-page-wrap">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-9">
                @if (session('success'))
                    <div class="gw-alert gw-alert-success mb-4">{{ session('success') }}</div>
                @endif

                @if ($justPlaced)
                    <div class="gw-panel p-4 p-md-5 text-center mb-4">
                        <div class="mb-3">
                            <span class="d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border: 1px solid var(--acc); color: var(--acc); background: rgba(168, 132, 90, 0.08);">
                                <i class="fas fa-check fa-lg"></i>
                            </span>
                        </div>
                        <h1 class="gw-page-title mb-2">Thank you for your order</h1>
                        <p class="gw-page-sub mb-0">
                            Your order <strong style="color: var(--ink);">{{ $order->code }}</strong> is placed. Pay cash on delivery.
                        </p>
                    </div>
                @endif

                @include('store.partials.order-timeline')

                <div class="gw-panel mb-4">
                    <div class="row g-3 mb-4 pb-3" style="border-bottom: 1px solid var(--stone);">
                        <div class="col-sm-6">
                            <div class="gw-label mb-1" style="color: var(--mut);">Status</div>
                            <span class="gw-badge gw-badge-accent">{{ $order->statusLabel() }}</span>
                        </div>
                        <div class="col-sm-6">
                            <div class="gw-label mb-1" style="color: var(--mut);">Payment</div>
                            <div class="fw-medium" style="color: var(--ink);">
                                {{ $order->payment?->methodLabel() ?? 'Cash on Delivery' }}
                                <span class="small fw-normal" style="color: var(--mut);">({{ $order->payment?->statusLabel() ?? 'Pending' }})</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="gw-label mb-1" style="color: var(--mut);">Customer</div>
                            <div class="fw-medium" style="color: var(--ink);">{{ $order->customer?->name }}</div>
                            <div class="small" style="color: var(--mut);">{{ $order->customer?->phone }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="gw-label mb-1" style="color: var(--mut);">Deliver to</div>
                            <div class="fw-medium" style="color: var(--ink);">{{ $order->shipping_address }}</div>
                            <div class="small" style="color: var(--mut);">{{ $order->shipping_city }} — {{ $order->shipping_pincode }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="gw-label mb-1" style="color: var(--mut);">Total</div>
                            <div style="font-family: 'Cormorant Garamond', Georgia, serif; font-size: 1.65rem; font-weight: 600; color: var(--ink);">
                                ₹{{ number_format((float) $order->total, 2) }}
                            </div>
                        </div>
                    </div>

                    <h2 class="gw-panel-title">
                        <span><i class="fas fa-couch me-2"></i>Items</span>
                    </h2>
                    @foreach ($order->items as $item)
                        <div class="d-flex justify-content-between gap-3 {{ ! $loop->last ? 'pb-3 mb-3' : '' }}" style="{{ ! $loop->last ? 'border-bottom: 1px solid var(--stone);' : '' }}">
                            <div>
                                <div class="fw-medium" style="color: var(--ink);">{{ $item->variant?->product?->name ?? $item->product?->name }}</div>
                                <div class="small" style="color: var(--mut);">{{ $item->variant?->label() }}</div>
                                <div class="small" style="color: var(--mut);">{{ $item->variant?->sku ?? $item->product?->code }} × {{ $item->quantity }}</div>
                            </div>
                            <div class="fw-semibold text-nowrap" style="color: var(--ink);">₹{{ number_format((float) $item->line_total, 0) }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                    <a href="{{ route('store.orders.index') }}" class="gw-btn gw-btn-outline text-center">My Orders</a>
                    <a href="{{ route('store.catalog') }}" class="gw-btn text-center">Continue Shopping</a>
                </div>
            </div>
        </div>
    </section>

    @include('store.partials.footer')
    @include('store.partials.whatsapp')
@endsection
