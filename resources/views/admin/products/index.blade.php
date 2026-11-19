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
                    <h5 class="card-title mb-0 flex-grow-1">Catalogue</h5>
                    <a href="{{ route('admin.products.create') }}" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> Add Product
                    </a>
                </div>
                <div class="card-body">
                    @if ($products->isEmpty())
                        <div class="text-center py-5">
                            <h5 class="mb-2">No products yet</h5>
                            <p class="text-muted mb-3">Create a product with its variants, then record quantity in a warehouse.</p>
                            <a href="{{ route('admin.products.create') }}" class="btn btn-success">Add Product</a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 w-100 js-datatable" data-order-column="2" data-unsearchable="0,8" data-label="products">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Code</th>
                                        <th>Model</th>
                                        <th>Path</th>
                                        <th>Variants</th>
                                        <th>From</th>
                                        <th>Stock</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($products as $product)
                                        @php
                                            $path = $product->path();
                                            $stock = (int) ($product->stock_total ?? 0);
                                        @endphp
                                        <tr>
                                            <td></td>
                                            <td class="fw-medium">{{ $product->code }}</td>
                                            <td>{{ $product->name }}</td>
                                            <td>{{ $path !== '' ? $path : ($product->category?->parent?->name ? $product->category->parent->name.' › '.$product->category->name : ($product->category?->name ?? '—')) }}</td>
                                            <td data-order="{{ $product->variants->count() }}">{{ $product->variants->count() }}</td>
                                            <td data-order="{{ $product->fromPrice() }}">₹{{ number_format($product->fromPrice(), 2) }}</td>
                                            <td data-order="{{ $stock }}">{{ $stock }}</td>
                                            <td data-order="{{ $product->isActive() ? 1 : 0 }}">
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
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
