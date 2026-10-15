@extends('layouts.agent')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-4 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <p class="text-uppercase fw-medium text-muted mb-0">Today's Sales</p>
                    <h4 class="fs-22 fw-semibold mt-3 mb-0">₹{{ number_format((float) $saleTotal, 2) }}</h4>
                    <p class="text-muted mb-0 mt-2">{{ $saleCount }} {{ $saleCount === 1 ? 'bill' : 'bills' }} at {{ $agent->name }}</p>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <p class="text-uppercase fw-medium text-muted mb-0">Commission Earned</p>
                    <h4 class="fs-22 fw-semibold mt-3 mb-0">₹{{ number_format((float) $commission, 2) }}</h4>
                    <p class="text-muted mb-0 mt-2">Across all saved bills</p>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <p class="text-uppercase fw-medium text-muted mb-0">New Sale</p>
                    <a href="{{ route('agent.sales.create') }}" class="btn btn-success mt-3">New Sale</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="card-title mb-0">Recent Sales</h5></div>
        <div class="card-body">
            @if ($recentSales->isEmpty())
                <p class="text-muted mb-0">No sales yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentSales as $order)
                                <tr>
                                    <td class="fw-medium"><a href="{{ route('agent.sales.show', $order) }}">{{ $order->code }}</a></td>
                                    <td>{{ $order->customer?->name ?? '—' }}</td>
                                    <td>₹{{ number_format((float) $order->total, 2) }}</td>
                                    <td>{{ $order->created_at?->format('d M Y, h:i A') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
