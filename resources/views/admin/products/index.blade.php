@extends('layouts.admin')

@section('title', 'Products')
@section('page_title', 'Products')

@section('breadcrumb')
    <li class="breadcrumb-item active">Products</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Products</h5>
                    <a href="{{ route('admin.products.create') }}" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> Add Product
                    </a>
                </div>
                <div class="card-body">
                    @if ($products->isEmpty())
                        <div class="text-center py-5">
                            <h5 class="mb-2">No products yet</h5>
                            <p class="text-muted mb-3">Create a product once, then record its quantity in a warehouse.</p>
                            <a href="{{ route('admin.products.create') }}" class="btn btn-success">Add Product</a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Selling Price</th>
                                        <th>Store</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($products as $product)
                                        <tr>
                                            <td>{{ $products->firstItem() + $loop->index }}</td>
                                            <td class="fw-medium">{{ $product->code }}</td>
                                            <td>{{ $product->name }}</td>
                                            <td>{{ $product->category?->name ?? '—' }}</td>
                                            <td>₹{{ number_format((float) $product->selling_price, 2) }}</td>
                                            <td>
                                                @if ($product->is_online)
                                                    <span class="badge bg-info-subtle text-info">Online</span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>
                                                @if ($product->isActive())
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                                    @include('admin.partials.edit-icon', ['url' => route('admin.products.edit', $product)])
                                                    @include('admin.partials.status-actions', [
                                                        'record' => $product,
                                                        'activateUrl' => route('admin.products.status', $product),
                                                    ])
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $products->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
