@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Today's Sales</p>
                    <h4 class="fs-22 fw-semibold ff-secondary mt-3 mb-0">₹{{ number_format((float) $saleTotal, 2) }}</h4>
                    <p class="text-muted mb-0 mt-2">{{ $saleCount }} {{ $saleCount === 1 ? 'bill' : 'bills' }}</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Waiting To Receive</p>
                    <h4 class="fs-22 fw-semibold ff-secondary mt-3 mb-0">{{ $waitingCount }}</h4>
                    <a href="{{ route('admin.transfers.index') }}" class="d-inline-block mt-2">Transfers</a>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Open Requests</p>
                    <h4 class="fs-22 fw-semibold ff-secondary mt-3 mb-0">{{ $openRequestCount }}</h4>
                    <a href="{{ route('admin.stock-requests.index') }}" class="d-inline-block mt-2">Stock Requests</a>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Draft Transfers</p>
                    <h4 class="fs-22 fw-semibold ff-secondary mt-3 mb-0">{{ $draftCount }}</h4>
                    <a href="{{ route('admin.transfers.index') }}" class="d-inline-block mt-2">Transfers</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-5">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Today By Branch</h5></div>
                <div class="card-body">
                    @if ($salesByBranch->isEmpty())
                        <p class="text-muted mb-0">No sales yet today.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Branch</th>
                                        <th>Bills</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($salesByBranch as $row)
                                        <tr>
                                            <td>{{ $row['branch'] }}</td>
                                            <td>{{ $row['bills'] }}</td>
                                            <td>₹{{ number_format($row['amount'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-7">
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
                                        <th>Branch</th>
                                        <th>Customer</th>
                                        <th>Total</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentSales as $order)
                                        <tr>
                                            <td class="fw-medium"><a href="{{ route('admin.orders.show', $order) }}">{{ $order->code }}</a></td>
                                            <td>{{ $order->branch?->name ?? '—' }}</td>
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
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Waiting At The Showroom</h5></div>
                <div class="card-body">
                    @if ($waitingTransfers->isEmpty())
                        <p class="text-muted mb-0">No dispatched transfer is waiting to be received.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Branch</th>
                                        <th>Dispatched</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($waitingTransfers as $transfer)
                                        <tr>
                                            <td class="fw-medium"><a href="{{ route('admin.transfers.edit', $transfer) }}">{{ $transfer->code }}</a></td>
                                            <td>{{ $transfer->branch?->name ?? '—' }}</td>
                                            <td>{{ $transfer->dispatched_at?->format('d M Y, h:i A') ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Open Stock Requests</h5></div>
                <div class="card-body">
                    @if ($openRequests->isEmpty())
                        <p class="text-muted mb-0">No branch is waiting for a transfer.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Branch</th>
                                        <th>Requested</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($openRequests as $stockRequest)
                                        <tr>
                                            <td class="fw-medium"><a href="{{ route('admin.stock-requests.show', $stockRequest) }}">{{ $stockRequest->code }}</a></td>
                                            <td>{{ $stockRequest->branch?->name ?? '—' }}</td>
                                            <td>{{ $stockRequest->created_at?->format('d M Y, h:i A') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
