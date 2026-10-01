@extends('layouts.admin')

@section('title', 'Sales By Product')
@section('page_title', 'Sales By Product')

@section('breadcrumb')
    <li class="breadcrumb-item active">Sales By Product</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Sales By Product</h5></div>
                <div class="card-body">
                    @include('admin.reports._dates')
                    @if ($rows->isEmpty())
                        <p class="text-muted mb-0">No sales in this period.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Code</th>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rows as $row)
                                        @php $product = $products->get($row->product_id); @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="fw-medium">{{ $product?->code ?? '—' }}</td>
                                            <td>{{ $product?->name ?? '—' }}</td>
                                            <td>{{ $row->quantity }}</td>
                                            <td>₹{{ number_format((float) $row->amount, 2) }}</td>
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
