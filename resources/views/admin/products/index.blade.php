@extends('layouts.admin')

@section('title', 'Products')
@section('page_title', 'Products')

@section('breadcrumb')
    <li class="breadcrumb-item active">Products</li>
@endsection

@section('content')
    @php
        $hasFilters = $filters['q'] !== ''
            || $filters['status'] !== ''
            || $filters['online'] !== ''
            || $filters['featured'] !== ''
            || $filters['project_category'] !== ''
            || $filters['room'] !== '';
    @endphp

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0 flex-grow-1">Catalogue</h5>
                    <a href="{{ route('admin.products.create') }}" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> Add Product
                    </a>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.products.index') }}" class="row g-2 mb-4">
                        <div class="col-md-3">
                            <label for="product_filter_q" class="form-label">Search</label>
                            <input type="text" class="form-control" id="product_filter_q" name="q" value="{{ $filters['q'] }}" placeholder="Code or model name">
                        </div>
                        <div class="col-md-2">
                            <label for="product_filter_project" class="form-label">Project</label>
                            <select class="form-select" id="product_filter_project" name="project_category" onchange="this.form.submit()">
                                <option value="" @selected($filters['project_category'] === '')>All</option>
                                @foreach ($filterProjects as $project)
                                    <option value="{{ $project }}" @selected($filters['project_category'] === $project)>{{ $project }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="product_filter_room" class="form-label">Room</label>
                            <select class="form-select" id="product_filter_room" name="room">
                                <option value="" @selected($filters['room'] === '')>All</option>
                                @foreach ($filterRooms as $room)
                                    <option value="{{ $room }}" @selected($filters['room'] === $room)>{{ $room }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label for="product_filter_status" class="form-label">Status</label>
                            <select class="form-select" id="product_filter_status" name="status">
                                <option value="" @selected($filters['status'] === '')>All</option>
                                <option value="active" @selected($filters['status'] === 'active')>Active</option>
                                <option value="inactive" @selected($filters['status'] === 'inactive')>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label for="product_filter_online" class="form-label">Online</label>
                            <select class="form-select" id="product_filter_online" name="online">
                                <option value="" @selected($filters['online'] === '')>All</option>
                                <option value="1" @selected($filters['online'] === '1')>Yes</option>
                                <option value="0" @selected($filters['online'] === '0')>No</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label for="product_filter_featured" class="form-label">Featured</label>
                            <select class="form-select" id="product_filter_featured" name="featured">
                                <option value="" @selected($filters['featured'] === '')>All</option>
                                <option value="1" @selected($filters['featured'] === '1')>Yes</option>
                                <option value="0" @selected($filters['featured'] === '0')>No</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            @if ($hasFilters)
                                <a href="{{ route('admin.products.index') }}" class="btn btn-light">Clear</a>
                            @endif
                        </div>
                    </form>

                    @if ($products->isEmpty())
                        <div class="text-center py-5">
                            @if ($hasFilters)
                                <h5 class="mb-2">No products match</h5>
                                <p class="text-muted mb-3">Try clearing the filters or searching a different code or name.</p>
                                <a href="{{ route('admin.products.index') }}" class="btn btn-light">Clear Filters</a>
                            @else
                                <h5 class="mb-2">No products yet</h5>
                                <p class="text-muted mb-3">Create a product with its variants, then record quantity in a warehouse.</p>
                                <a href="{{ route('admin.products.create') }}" class="btn btn-success">Add Product</a>
                            @endif
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
                                            <td>
                                                {{ $product->name }}
                                                @if ($product->is_online)
                                                    <span class="badge bg-info-subtle text-info ms-1">Online</span>
                                                @endif
                                                @if ($product->is_featured)
                                                    <span class="badge bg-warning-subtle text-warning ms-1">Featured</span>
                                                @endif
                                            </td>
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
