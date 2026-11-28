@extends('layouts.admin')

@section('title', 'Stock On Hand')
@section('page_title', 'Reports & Analytics')

@section('breadcrumb')
    <li class="breadcrumb-item active">Stock On Hand</li>
@endsection

@section('content')
    @include('admin.partials.reports-tabs')

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Stock On Hand</h5></div>
                <div class="card-body">
                    <p class="text-muted">Quantities held now across warehouses, branches, and agent shops. In transit is dispatched but not yet received.</p>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4 col-xl-2">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted small">Warehouses</div>
                                <div class="fs-4 fw-semibold">{{ number_format($summary['warehouses']) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 col-xl-2">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted small">Branches</div>
                                <div class="fs-4 fw-semibold">{{ number_format($summary['branches']) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 col-xl-2">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted small">Agent shops</div>
                                <div class="fs-4 fw-semibold">{{ number_format($summary['agents']) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 col-xl-2">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted small">On hand total</div>
                                <div class="fs-4 fw-semibold">{{ number_format($summary['on_hand']) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 col-xl-2">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted small">In transit</div>
                                <div class="fs-4 fw-semibold">{{ number_format($summary['in_transit']) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 col-xl-2">
                            <div class="border rounded p-3 h-100 bg-light">
                                <div class="text-muted small">Matched total</div>
                                <div class="fs-4 fw-semibold">{{ number_format($summary['matched_total']) }}</div>
                                <div class="text-muted small">On hand + in transit</div>
                            </div>
                        </div>
                    </div>

                    @if ($stocks->isEmpty())
                        <p class="text-muted mb-0">No stock yet.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Place</th>
                                        <th>SKU</th>
                                        <th>Product</th>
                                        <th>Variant</th>
                                        <th>Quantity</th>
                                        <th>Updated</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($stocks as $stock)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $stock->warehouse?->name ?? $stock->branch?->name ?? $stock->agent?->name ?? '—' }}</td>
                                            <td class="fw-medium">{{ $stock->variant?->sku }}</td>
                                            <td>{{ $stock->variant?->product?->name }}</td>
                                            <td>{{ $stock->variant?->label() }}</td>
                                            <td>{{ $stock->quantity }}</td>
                                            <td>{{ $stock->updated_at?->format('d M Y, h:i A') }}</td>
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
