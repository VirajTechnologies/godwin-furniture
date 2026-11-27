@extends('layouts.admin')

@section('title', $customer->name)
@section('page_title', 'Registration')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customers</a></li>
    <li class="breadcrumb-item active">{{ $customer->name }}</li>
@endsection

@section('content')
    @include('admin.partials.registration-tabs')

    <div class="row">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Customer</h5>
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-light">Back</a>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted">Name</dt>
                        <dd class="col-sm-8">{{ $customer->name }}</dd>
                        <dt class="col-sm-4 text-muted">Phone</dt>
                        <dd class="col-sm-8">{{ $customer->phone }}</dd>
                        <dt class="col-sm-4 text-muted">Email</dt>
                        <dd class="col-sm-8">{{ $customer->email ?: '—' }}</dd>
                        <dt class="col-sm-4 text-muted">Status</dt>
                        <dd class="col-sm-8">
                            @if ($customer->isActive())
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">Inactive</span>
                            @endif
                        </dd>
                        <dt class="col-sm-4 text-muted">Login</dt>
                        <dd class="col-sm-8">{{ $customer->user?->email ?? '—' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Summary</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <tbody>
                                <tr><td>Orders</td><td class="text-end fw-medium">{{ $summary['orders'] }}</td></tr>
                                <tr><td>Total spend</td><td class="text-end fw-medium">₹{{ number_format($summary['total'], 2) }}</td></tr>
                                <tr><td>Online</td><td class="text-end fw-medium">{{ $summary['online'] }}</td></tr>
                                <tr><td>Showroom</td><td class="text-end fw-medium">{{ $summary['branch'] }}</td></tr>
                                <tr><td>Agent</td><td class="text-end fw-medium">{{ $summary['agent'] }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Addresses</h5></div>
                <div class="card-body">
                    @forelse ($customer->addresses as $address)
                        <div class="mb-3 {{ $loop->last ? 'mb-0' : '' }}">
                            <div class="fw-medium">{{ $address->displayLabel() }}@if ($address->is_default) <span class="badge bg-success-subtle text-success">Default</span>@endif</div>
                            <div class="text-muted small">{{ $address->oneLine() }}</div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No saved addresses.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Orders</h5></div>
                <div class="card-body">
                    @if ($orders->isEmpty())
                        <p class="text-muted mb-0">No orders for this customer.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Channel</th>
                                        <th>Place</th>
                                        <th>Status</th>
                                        <th class="text-end">Total</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($orders as $order)
                                        <tr>
                                            <td class="fw-medium"><a href="{{ route('admin.orders.show', $order) }}">{{ $order->code }}</a></td>
                                            <td>{{ ucfirst($order->channel) }}</td>
                                            <td>{{ $order->branch?->name ?? $order->agent?->name ?? ($order->isOnline() ? 'Online' : '—') }}</td>
                                            <td>{{ $order->statusLabel() }}</td>
                                            <td class="text-end">₹{{ number_format((float) $order->total, 2) }}</td>
                                            <td>{{ $order->created_at?->format('d M Y, h:i A') }}</td>
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
