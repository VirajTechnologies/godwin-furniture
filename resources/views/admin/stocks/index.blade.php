@extends('layouts.admin')

@section('title', 'Warehouse Stock')
@section('page_title', 'Warehouse Stock')

@section('breadcrumb')
    <li class="breadcrumb-item active">Warehouse Stock</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Warehouse Stock</h5>
                    <a href="{{ route('admin.stocks.create') }}" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> Add Stock
                    </a>
                </div>
                <div class="card-body">
                    @if ($stocks->isEmpty())
                        <div class="text-center py-5">
                            <h5 class="mb-2">No warehouse stock yet</h5>
                            <p class="text-muted mb-3">Create a product, then record how many pieces are in a warehouse.</p>
                            <a href="{{ route('admin.stocks.create') }}" class="btn btn-success">Add Stock</a>
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
                                        <th>Warehouse</th>
                                        <th>Quantity</th>
                                        <th>Updated</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($stocks as $stock)
                                        <tr>
                                            <td>{{ $stocks->firstItem() + $loop->index }}</td>
                                            <td class="fw-medium">{{ $stock->product?->code }}</td>
                                            <td>{{ $stock->product?->name }}</td>
                                            <td>{{ $stock->product?->category?->name ?? '—' }}</td>
                                            <td>{{ $stock->warehouse?->name ?? '—' }}</td>
                                            <td>{{ $stock->quantity }}</td>
                                            <td>{{ $stock->updated_at?->format('d M Y, h:i A') }}</td>
                                            <td>
                                                @include('admin.partials.edit-icon', ['url' => route('admin.stocks.edit', $stock)])
                                            </td>
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
