@extends('layouts.store')

@section('title', 'My Orders')
@section('body_class', 'gw-theme-body')

@section('content')
    @include('store.partials.header')

    <section class="gw-page-head">
        <div class="gw-page-head-inner">
            <nav aria-label="breadcrumb">
                <ol class="gw-breadcrumb">
                    <li><a href="{{ route('store.home') }}">Home</a></li>
                    <li class="gw-bc-sep">/</li>
                    <li><a href="{{ route('store.account') }}">My Account</a></li>
                    <li class="gw-bc-sep">/</li>
                    <li class="is-current" aria-current="page">My Orders</li>
                </ol>
            </nav>
            <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
                <div>
                    <h1 class="gw-page-title">My Orders</h1>
                    <p class="gw-page-sub">Track furniture orders placed on the website.</p>
                </div>
                <a href="{{ route('store.catalog') }}" class="gw-view-link">
                    <i class="fas fa-arrow-left me-1"></i> Continue Shopping
                </a>
            </div>
        </div>
    </section>

    <section class="gw-page-wrap">
        @if (session('error'))
            <div class="gw-alert gw-alert-danger">{{ session('error') }}</div>
        @endif

        @if ($orders->isEmpty())
            <div class="gw-panel p-5 text-center">
                <h2 class="gw-panel-subtitle mb-2">No orders yet</h2>
                <p class="gw-page-sub mb-4">When you place an order online, it will show up here.</p>
                <a href="{{ route('store.catalog') }}" class="gw-btn">Browse Catalog</a>
            </div>
        @else
            <div class="gw-panel p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="gw-table mb-0">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                <tr>
                                    <td class="fw-semibold">{{ $order->code }}</td>
                                    <td style="color: var(--mut);">{{ $order->created_at?->format('d M Y') }}</td>
                                    <td style="color: var(--mut);">{{ $order->items->sum('quantity') }}</td>
                                    <td class="fw-semibold">₹{{ number_format((float) $order->total, 2) }}</td>
                                    <td>
                                        <span class="gw-badge {{ match ($order->status) {
                                            \App\Models\Order::STATUS_PLACED => 'gw-badge-accent',
                                            \App\Models\Order::STATUS_CONFIRMED => 'gw-badge-accent',
                                            \App\Models\Order::STATUS_DISPATCHED => 'gw-badge-dark',
                                            \App\Models\Order::STATUS_COMPLETED => 'gw-badge-success',
                                            default => '',
                                        } }}">
                                            {{ $order->statusLabel() }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('store.orders.show', $order) }}" class="gw-btn gw-btn-sm gw-btn-outline">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($orders->hasPages())
                <div class="mt-4 gw-pagination">{{ $orders->links() }}</div>
            @endif
        @endif
    </section>

    @include('store.partials.footer')
    @include('store.partials.whatsapp')
@endsection
