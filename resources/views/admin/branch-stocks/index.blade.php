@extends('layouts.admin')

@section('title', 'Branch Stock')
@section('page_title', 'Branch Stock')

@section('breadcrumb')
    <li class="breadcrumb-item active">Branch Stock</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Branch Stock</h5>
                </div>
                <div class="card-body">
                    @if ($stocks->isEmpty())
                        <div class="text-center py-5">
                            <h5 class="mb-2">No branch stock yet</h5>
                            <p class="text-muted mb-0">A branch quantity increases only when a transfer is received.</p>
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
                                        <th>Branch</th>
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
                                            <td>{{ $stock->branch?->name ?? '—' }}</td>
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
