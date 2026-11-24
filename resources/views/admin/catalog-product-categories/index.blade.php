@extends('layouts.admin')

@section('title', 'Product Categories')
@section('page_title', 'Product Categories')

@section('breadcrumb')
    <li class="breadcrumb-item active">Product Categories</li>
@endsection

@section('content')
    @php
        $hasFilters = $filters['q'] !== ''
            || $filters['status'] !== ''
            || $filters['catalog_room_id'] !== '';
    @endphp

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Product Categories</h5>
                    <a href="{{ route('admin.catalog-product-categories.create') }}" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> Add Product Category
                    </a>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.catalog-product-categories.index') }}" class="row g-2 mb-4">
                        <div class="col-md-4">
                            <label for="q" class="form-label">Search</label>
                            <input type="text" class="form-control" id="q" name="q" value="{{ $filters['q'] }}" placeholder="Name">
                        </div>
                        <div class="col-md-3">
                            <label for="catalog_room_id" class="form-label">Room</label>
                            <select class="form-select" id="catalog_room_id" name="catalog_room_id">
                                <option value="" @selected($filters['catalog_room_id'] === '')>All</option>
                                @foreach ($filterRooms as $room)
                                    <option value="{{ $room->id }}" @selected((string) $filters['catalog_room_id'] === (string) $room->id)>
                                        {{ $room->projectCategory?->name ? $room->projectCategory->name.' › ' : '' }}{{ $room->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="" @selected($filters['status'] === '')>All</option>
                                <option value="active" @selected($filters['status'] === 'active')>Active</option>
                                <option value="inactive" @selected($filters['status'] === 'inactive')>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            @if ($hasFilters)
                                <a href="{{ route('admin.catalog-product-categories.index') }}" class="btn btn-light">Clear</a>
                            @endif
                        </div>
                    </form>

                    @if ($catalogProductCategories->isEmpty())
                        <div class="text-center py-5">
                            @if ($hasFilters)
                                <h5 class="mb-2">No product categories match</h5>
                                <p class="text-muted mb-3">Try clearing the filters.</p>
                                <a href="{{ route('admin.catalog-product-categories.index') }}" class="btn btn-light">Clear Filters</a>
                            @else
                                <h5 class="mb-2">No Product Categories yet</h5>
                                <a href="{{ route('admin.catalog-product-categories.create') }}" class="btn btn-success">Add Product Category</a>
                            @endif
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Room</th>
                                        <th>Name</th>
                                        <th class="text-end">Sort</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($catalogProductCategories as $catalogProductCategory)
                                        <tr>
                                            <td>{{ $catalogProductCategories->firstItem() + $loop->index }}</td>
                                            <td>{{ $catalogProductCategory->room?->name }}</td>
                                            <td class="fw-medium">{{ $catalogProductCategory->name }}</td>
                                            <td class="text-end">{{ $catalogProductCategory->sort_order }}</td>
                                            <td>
                                                @if ($catalogProductCategory->isActive())
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                                    @include('admin.partials.edit-icon', ['url' => route('admin.catalog-product-categories.edit', $catalogProductCategory)])
                                                    @include('admin.partials.status-actions', [
                                                        'record' => $catalogProductCategory,
                                                        'activateUrl' => route('admin.catalog-product-categories.status', $catalogProductCategory),
                                                    ])
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $catalogProductCategories->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
