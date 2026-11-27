@extends('layouts.admin')

@section('title', 'Customers')
@section('page_title', 'Customers')

@section('breadcrumb')
    <li class="breadcrumb-item active">Customers</li>
@endsection

@section('content')
    @php
        $hasFilters = $filters['q'] !== '';
    @endphp

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Customers</h5>
                    <span class="text-muted small">View only</span>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.customers.index') }}" class="row g-2 mb-4">
                        <div class="col-md-4">
                            <label for="customer_filter_q" class="form-label">Search</label>
                            <input type="text" class="form-control" id="customer_filter_q" name="q" value="{{ $filters['q'] }}" placeholder="Name, phone or email">
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            @if ($hasFilters)
                                <a href="{{ route('admin.customers.index') }}" class="btn btn-light">Clear</a>
                            @endif
                        </div>
                    </form>

                    @if ($customers->isEmpty())
                        <div class="text-center py-5">
                            @if ($hasFilters)
                                <h5 class="mb-2">No customers match</h5>
                                <a href="{{ route('admin.customers.index') }}" class="btn btn-light">Clear Filters</a>
                            @else
                                <h5 class="mb-2">No customers yet</h5>
                                <p class="text-muted mb-0">Customers appear here after store, branch, or agent sales.</p>
                            @endif
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Orders</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($customers as $customer)
                                        <tr>
                                            <td>{{ $customers->firstItem() + $loop->index }}</td>
                                            <td class="fw-medium">{{ $customer->name }}</td>
                                            <td>{{ $customer->phone }}</td>
                                            <td>{{ $customer->email ?: '—' }}</td>
                                            <td>{{ $customer->orders_count }}</td>
                                            <td>₹{{ number_format((float) ($customer->orders_total ?? 0), 2) }}</td>
                                            <td>
                                                @if ($customer->isActive())
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-soft-secondary" title="View">
                                                    <i class="ri-eye-line"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $customers->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
