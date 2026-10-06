@extends('layouts.admin')

@section('title', 'Sales')
@section('page_title', 'Sales')

@section('breadcrumb')
    <li class="breadcrumb-item active">Sales</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h5 class="card-title mb-0">Sales</h5>
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('admin.orders.index') }}" class="btn {{ $channel === '' ? 'btn-primary' : 'btn-soft-primary' }}">All</a>
                        <a href="{{ route('admin.orders.index', ['channel' => 'online']) }}" class="btn {{ $channel === 'online' ? 'btn-primary' : 'btn-soft-primary' }}">Online</a>
                        <a href="{{ route('admin.orders.index', ['channel' => 'branch']) }}" class="btn {{ $channel === 'branch' ? 'btn-primary' : 'btn-soft-primary' }}">Branch</a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($orders->isEmpty())
                        <div class="text-center py-5">
                            <h5 class="mb-2">No sales yet</h5>
                            <p class="text-muted mb-0">Branch counter sales and online store orders appear here.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Code</th>
                                        <th>Channel</th>
                                        <th>Customer</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Payment</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($orders as $order)
                                        <tr>
                                            <td>{{ $orders->firstItem() + $loop->index }}</td>
                                            <td class="fw-medium">{{ $order->code }}</td>
                                            <td>
                                                @if ($order->channel === \App\Models\Order::CHANNEL_ONLINE)
                                                    Online
                                                @else
                                                    {{ $order->branch?->name ?? 'Branch' }}
                                                @endif
                                            </td>
                                            <td>{{ $order->customer?->name }}</td>
                                            <td>₹{{ number_format((float) $order->total, 2) }}</td>
                                            <td>
                                                <span class="badge {{ match ($order->status) {
                                                    \App\Models\Order::STATUS_PLACED => 'bg-warning-subtle text-warning',
                                                    \App\Models\Order::STATUS_CONFIRMED => 'bg-info-subtle text-info',
                                                    \App\Models\Order::STATUS_DISPATCHED => 'bg-primary-subtle text-primary',
                                                    \App\Models\Order::STATUS_COMPLETED => 'bg-success-subtle text-success',
                                                    default => 'bg-secondary-subtle text-secondary',
                                                } }}">{{ $order->statusLabel() }}</span>
                                            </td>
                                            <td>{{ $order->payment?->methodLabel() ?? '—' }}</td>
                                            <td>{{ $order->created_at?->format('d M Y, h:i A') }}</td>
                                            <td>
                                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-soft-primary btn-icon" title="View" aria-label="View">
                                                    <i class="ri-eye-fill"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $orders->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
