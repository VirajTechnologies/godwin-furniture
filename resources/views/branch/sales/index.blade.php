@extends('layouts.branch')

@section('title', 'Sales')
@section('page_title', 'Sales')

@section('breadcrumb')
    <li class="breadcrumb-item active">Sales</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Sales</h5>
                    <a href="{{ route('branch.sales.create') }}" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> New Sale
                    </a>
                </div>
                <div class="card-body">
                    @if ($orders->isEmpty())
                        <div class="text-center py-5">
                            <h5 class="mb-2">No sales yet</h5>
                            <p class="text-muted mb-3">A sale reduces the quantity held at this branch.</p>
                            <a href="{{ route('branch.sales.create') }}" class="btn btn-success">New Sale</a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Code</th>
                                        <th>Customer</th>
                                        <th>Total</th>
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
                                            <td>{{ $order->customer?->name }}</td>
                                            <td>₹{{ number_format((float) $order->total, 2) }}</td>
                                            <td>{{ $order->payment?->methodLabel() ?? '—' }}</td>
                                            <td>{{ $order->created_at?->format('d M Y, h:i A') }}</td>
                                            <td>
                                                <a href="{{ route('branch.sales.show', $order) }}" class="btn btn-sm btn-soft-primary btn-icon" title="View" aria-label="View">
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
