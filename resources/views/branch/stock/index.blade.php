@extends('layouts.branch')

@section('title', 'Stock')
@section('page_title', 'Stock')

@section('breadcrumb')
    <li class="breadcrumb-item active">Stock</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Stock At This Branch</h5></div>
                <div class="card-body">
                    @if ($stocks->isEmpty())
                        <div class="text-center py-5">
                            <h5 class="mb-2">No stock yet</h5>
                            <p class="text-muted mb-0">Quantity increases when a transfer is received, and decreases when a sale is saved.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Code</th>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Subcategory</th>
                                        <th>Quantity</th>
                                        <th>Updated</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($stocks as $stock)
                                        <tr>
                                            <td>{{ $stocks->firstItem() + $loop->index }}</td>
                                            <td class="fw-medium">{{ $stock->product?->code }}</td>
                                            <td>{{ $stock->product?->name }}</td>
                                            <td>{{ $stock->product?->category?->parent?->name ?? '—' }}</td>
                                            <td>{{ $stock->product?->category?->name ?? '—' }}</td>
                                            <td>{{ $stock->quantity }}</td>
                                            <td>{{ $stock->updated_at?->format('d M Y, h:i A') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $stocks->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
