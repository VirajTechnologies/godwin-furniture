@extends('layouts.admin')

@section('title', 'Reports & Analytics')
@section('page_title', 'Reports & Analytics')

@section('breadcrumb')
    <li class="breadcrumb-item active">Overview</li>
@endsection

@section('content')
    @include('admin.partials.reports-tabs')

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <p class="text-muted mb-0">Branch, warehouse and product summary for the selected period.</p>
        <a href="{{ route('admin.reports.export', ['from' => $from, 'to' => $to]) }}" class="btn btn-success">
            <i class="ri-download-2-line align-bottom me-1"></i> Download CSV
        </a>
    </div>

    @include('admin.reports._dates')

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Branch-wise</h5></div>
                <div class="card-body">
                    @if ($branches->isEmpty())
                        <p class="text-muted mb-0">No branch or shop activity in this period.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Branch</th>
                                        <th>Sales</th>
                                        <th>Revenue</th>
                                        <th>Stock</th>
                                        <th>Customers</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($branches as $row)
                                        <tr>
                                            <td class="fw-medium">{{ $row->name }}</td>
                                            <td>{{ $row->sales }}</td>
                                            <td>₹{{ number_format($row->revenue, 2) }}</td>
                                            <td>{{ $row->stock }}</td>
                                            <td>{{ $row->customers }}</td>
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
                <div class="card-header"><h5 class="card-title mb-0">Warehouse-wise</h5></div>
                <div class="card-body">
                    @if ($warehouses->isEmpty())
                        <p class="text-muted mb-0">No warehouses yet.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Warehouse</th>
                                        <th>Stock</th>
                                        <th>Pending dispatch</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($warehouses as $row)
                                        <tr>
                                            <td class="fw-medium">{{ $row->name }}</td>
                                            <td>{{ $row->stock }}</td>
                                            <td>{{ $row->pending_dispatch }}</td>
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
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Product-wise</h5></div>
                <div class="card-body">
                    @if ($products->isEmpty())
                        <p class="text-muted mb-0">No product sales or stock to show.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Sold</th>
                                        <th>Revenue</th>
                                        <th>Stock</th>
                                        <th>Movement</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($products as $row)
                                        <tr>
                                            <td class="fw-medium">{{ $row->name }}</td>
                                            <td>{{ $row->sold }}</td>
                                            <td>₹{{ number_format($row->revenue, 2) }}</td>
                                            <td>{{ $row->stock }}</td>
                                            <td>
                                                @if ($row->movement === 'Best seller')
                                                    <span class="badge bg-success-subtle text-success">Best seller</span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning">Slow</span>
                                                @endif
                                            </td>
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
