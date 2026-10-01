@extends('layouts.branch')

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
                    <p class="text-muted mb-0 mt-2">{{ $saleCount }} {{ $saleCount === 1 ? 'bill' : 'bills' }} at {{ $branch->name }}</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Out Of Stock</p>
                    <h4 class="fs-22 fw-semibold ff-secondary mt-3 mb-0">{{ $outOfStockCount }}</h4>
                    <a href="{{ route('branch.stock.index') }}" class="d-inline-block mt-2">Stock</a>
                </div>
            </div>
        </div>
        @if ($manager)
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Waiting To Receive</p>
                        <h4 class="fs-22 fw-semibold ff-secondary mt-3 mb-0">{{ $waitingCount }}</h4>
                        <a href="{{ route('branch.transfers.index') }}" class="d-inline-block mt-2">Receive</a>
                    </div>
                </div>
            </div>
        @endif
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">New Sale</p>
                    <a href="{{ route('branch.sales.create') }}" class="btn btn-success mt-3">New Sale</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
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
                                        <th>Customer</th>
                                        <th>Total</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentSales as $order)
                                        <tr>
                                            <td class="fw-medium"><a href="{{ route('branch.sales.show', $order) }}">{{ $order->code }}</a></td>
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
        <div class="col-xl-5">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Stock On Hand</h5></div>
                <div class="card-body">
                    @if ($stocks->isEmpty())
                        <p class="text-muted mb-0">No stock at this branch yet.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($stocks as $stock)
                                        <tr>
                                            <td class="fw-medium">{{ $stock->product?->code }}</td>
                                            <td>{{ $stock->product?->name }}</td>
                                            <td class="{{ $stock->quantity === 0 ? 'text-danger' : '' }}">{{ $stock->quantity }}</td>
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

    @if ($manager)
        <div class="row">
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Waiting To Receive</h5></div>
                    <div class="card-body">
                        @if ($waitingTransfers->isEmpty())
                            <p class="text-muted mb-0">No dispatched transfer is waiting to be received.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Code</th>
                                            <th>Dispatched</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($waitingTransfers as $transfer)
                                            <tr>
                                                <td class="fw-medium"><a href="{{ route('branch.transfers.show', $transfer) }}">{{ $transfer->code }}</a></td>
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
                    <div class="card-header"><h5 class="card-title mb-0">Stock Requests</h5></div>
                    <div class="card-body">
                        @if ($requests->isEmpty())
                            <p class="text-muted mb-0">No stock request is waiting on head office.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Code</th>
                                            <th>Status</th>
                                            <th>Requested</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($requests as $stockRequest)
                                            <tr>
                                                <td class="fw-medium"><a href="{{ route('branch.stock-requests.show', $stockRequest) }}">{{ $stockRequest->code }}</a></td>
                                                <td>{{ $stockRequest->label() }}</td>
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
    @endif
@endsection
