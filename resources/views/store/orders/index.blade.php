@extends('layouts.store')

@section('title', 'My Orders')
@section('body_class', 'bg-light')

@section('content')
    @include('store.partials.header')

    <section class="bg-white border-bottom py-3">
        <div class="container-fluid px-4 px-lg-5">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                <div>
                    <h2 class="font-heading fw-bold text-dark m-0">My Orders</h2>
                    <p class="text-muted small m-0">Track furniture orders placed on the website.</p>
                </div>
                <a href="{{ route('store.catalog') }}" class="text-amber font-heading fw-semibold text-decoration-none small">
                    <i class="fas fa-arrow-left me-1"></i> Continue Shopping
                </a>
            </div>
        </div>
    </section>

    <section class="container-fluid px-4 px-lg-5 my-4">
        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm rounded-3 font-heading">{{ session('error') }}</div>
        @endif

        @if ($orders->isEmpty())
            <div class="card border-0 shadow-sm rounded-4 p-5 bg-white text-center">
                <h5 class="font-heading fw-bold mb-2">No orders yet</h5>
                <p class="text-muted mb-3">When you place an order online, it will show up here.</p>
                <a href="{{ route('store.catalog') }}" class="btn btn-primary-luxury font-heading fw-bold">Browse Catalog</a>
            </div>
        @else
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr class="font-heading small text-muted">
                                <th class="ps-4 py-3">Order</th>
                                <th class="py-3">Date</th>
                                <th class="py-3">Items</th>
                                <th class="py-3">Total</th>
                                <th class="py-3">Status</th>
                                <th class="py-3 pe-4 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                <tr class="font-heading">
                                    <td class="ps-4 fw-semibold text-dark">{{ $order->code }}</td>
                                    <td class="text-muted small">{{ $order->created_at?->format('d M Y') }}</td>
                                    <td class="text-muted small">{{ $order->items->sum('quantity') }}</td>
                                    <td class="fw-semibold text-dark">₹{{ number_format((float) $order->total, 2) }}</td>
                                    <td>
                                        <span class="badge {{ match ($order->status) {
                                            \App\Models\Order::STATUS_PLACED => 'bg-warning text-dark',
                                            \App\Models\Order::STATUS_CONFIRMED => 'bg-info text-dark',
                                            \App\Models\Order::STATUS_DISPATCHED => 'bg-primary',
                                            \App\Models\Order::STATUS_COMPLETED => 'bg-success',
                                            default => 'bg-secondary',
                                        } }}">
                                            {{ $order->statusLabel() }}
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="{{ route('store.orders.show', $order) }}" class="btn btn-sm btn-outline-secondary font-heading fw-semibold">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3">{{ $orders->links() }}</div>
        @endif
    </section>

    @include('store.partials.footer')
    @include('store.partials.whatsapp')
@endsection
