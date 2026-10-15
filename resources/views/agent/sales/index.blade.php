@extends('layouts.agent')

@section('title', 'Sales')
@section('page_title', 'Sales')

@section('breadcrumb')
    <li class="breadcrumb-item active">Sales</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h5 class="card-title mb-0 flex-grow-1">Sales</h5>
            <a href="{{ route('agent.sales.create') }}" class="btn btn-success">New Sale</a>
        </div>
        <div class="card-body">
            @if ($orders->isEmpty())
                <p class="text-muted mb-0">No sales yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>SR No.</th>
                                <th>Code</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Commission</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                <tr>
                                    <td>{{ $orders->firstItem() + $loop->index }}</td>
                                    <td class="fw-medium"><a href="{{ route('agent.sales.show', $order) }}">{{ $order->code }}</a></td>
                                    <td>{{ $order->customer?->name ?? '—' }}</td>
                                    <td>₹{{ number_format((float) $order->total, 2) }}</td>
                                    <td>₹{{ number_format((float) $order->items->sum('commission_amount'), 2) }}</td>
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
@endsection
