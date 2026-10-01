@extends('layouts.admin')

@section('title', 'Stock On Hand')
@section('page_title', 'Stock On Hand')

@section('breadcrumb')
    <li class="breadcrumb-item active">Stock On Hand</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Stock On Hand</h5></div>
                <div class="card-body">
                    <p class="text-muted">This is the quantity held now, at the warehouse and at each branch.</p>
                    @if ($stocks->isEmpty())
                        <p class="text-muted mb-0">No stock yet.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Place</th>
                                        <th>Code</th>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Updated</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($stocks as $stock)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $stock->warehouse?->name ?? $stock->branch?->name ?? '—' }}</td>
                                            <td class="fw-medium">{{ $stock->product?->code }}</td>
                                            <td>{{ $stock->product?->name }}</td>
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
