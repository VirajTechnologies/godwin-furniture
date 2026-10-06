@extends('layouts.store')

@section('title', 'Order '.$order->code)
@section('body_class', 'bg-light')

@section('content')
    @include('store.partials.header')

    <section class="container-fluid px-4 px-lg-5 my-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                @if (session('success'))
                    <div class="alert alert-success border-0 shadow-sm rounded-3 font-heading mb-4">{{ session('success') }}</div>
                @endif

                @if ($justPlaced)
                    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white text-center mb-4">
                        <div class="mb-3">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10 text-success" style="width: 64px; height: 64px;">
                                <i class="fas fa-check fa-lg"></i>
                            </span>
                        </div>
                        <h2 class="font-heading fw-bold text-dark mb-2">Thank you for your order</h2>
                        <p class="text-muted font-heading mb-0">Your order <span class="fw-bold text-dark">{{ $order->code }}</span> is placed. Pay cash on delivery.</p>
                    </div>
                @else
                    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-4">
                        <div>
                            <h2 class="font-heading fw-bold text-dark m-0">Order {{ $order->code }}</h2>
                            <p class="text-muted small m-0">Placed {{ $order->created_at?->format('d M Y, h:i A') }}</p>
                        </div>
                        <a href="{{ route('store.orders.index') }}" class="text-amber font-heading fw-semibold text-decoration-none small">
                            <i class="fas fa-arrow-left me-1"></i> All Orders
                        </a>
                    </div>
                @endif

                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <div class="small text-muted font-heading">Status</div>
                            <div class="fw-semibold font-heading text-dark">{{ $order->statusLabel() }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="small text-muted font-heading">Payment</div>
                            <div class="fw-semibold font-heading text-dark">
                                {{ $order->payment?->methodLabel() ?? 'Cash on Delivery' }}
                                <span class="text-muted fw-normal small">({{ $order->payment?->statusLabel() ?? 'Pending' }})</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="small text-muted font-heading">Customer</div>
                            <div class="fw-semibold font-heading text-dark">{{ $order->customer?->name }}</div>
                            <div class="small font-heading text-muted">{{ $order->customer?->phone }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="small text-muted font-heading">Deliver to</div>
                            <div class="fw-semibold font-heading text-dark">{{ $order->shipping_address }}</div>
                            <div class="small font-heading text-muted">{{ $order->shipping_city }} — {{ $order->shipping_pincode }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="small text-muted font-heading">Total</div>
                            <div class="fw-bold font-heading text-dark fs-5">₹{{ number_format((float) $order->total, 2) }}</div>
                        </div>
                    </div>

                    <h5 class="font-heading fw-bold text-dark pb-3 border-bottom mb-3">Items</h5>
                    @foreach ($order->items as $item)
                        <div class="d-flex justify-content-between gap-3 font-heading {{ ! $loop->last ? 'pb-3 border-bottom mb-3' : '' }}">
                            <div>
                                <div class="fw-semibold text-dark">{{ $item->product?->name }}</div>
                                <div class="small text-muted">{{ $item->product?->code }} × {{ $item->quantity }}</div>
                            </div>
                            <div class="fw-bold text-dark text-nowrap">₹{{ number_format((float) $item->line_total, 0) }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                    <a href="{{ route('store.orders.index') }}" class="btn btn-outline-secondary font-heading fw-semibold px-4">My Orders</a>
                    <a href="{{ route('store.catalog') }}" class="btn btn-primary-luxury font-heading fw-bold px-4">Continue Shopping</a>
                </div>
            </div>
        </div>
    </section>

    @include('store.partials.footer')
    @include('store.partials.whatsapp')
@endsection
