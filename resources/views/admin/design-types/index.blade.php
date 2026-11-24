@extends('layouts.admin')

@section('title', 'Design Types')
@section('page_title', 'Design Types')

@section('breadcrumb')
    <li class="breadcrumb-item active">Design Types</li>
@endsection

@section('content')
    @php
        $hasFilters = $filters['q'] !== ''
            || $filters['status'] !== ''
            || $filters['catalog_product_category_id'] !== '';
    @endphp

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Design Types</h5>
                    <a href="{{ route('admin.design-types.create') }}" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> Add Design Type
                    </a>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.design-types.index') }}" class="row g-2 mb-4">
                        <div class="col-md-4">
                            <label for="q" class="form-label">Search</label>
                            <input type="text" class="form-control" id="q" name="q" value="{{ $filters['q'] }}" placeholder="Name">
                        </div>
                        <div class="col-md-3">
                            <label for="catalog_product_category_id" class="form-label">Product Category</label>
                            <select class="form-select" id="catalog_product_category_id" name="catalog_product_category_id">
                                <option value="" @selected($filters['catalog_product_category_id'] === '')>All</option>
                                @foreach ($filterProductCategories as $category)
                                    <option value="{{ $category->id }}" @selected((string) $filters['catalog_product_category_id'] === (string) $category->id)>
                                        {{ $category->room?->name ? $category->room->name.' › ' : '' }}{{ $category->name }}
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
                                <a href="{{ route('admin.design-types.index') }}" class="btn btn-light">Clear</a>
                            @endif
                        </div>
                    </form>

                    @if ($designTypes->isEmpty())
                        <div class="text-center py-5">
                            @if ($hasFilters)
                                <h5 class="mb-2">No design types match</h5>
                                <p class="text-muted mb-3">Try clearing the filters.</p>
                                <a href="{{ route('admin.design-types.index') }}" class="btn btn-light">Clear Filters</a>
                            @else
                                <h5 class="mb-2">No Design Types yet</h5>
                                <a href="{{ route('admin.design-types.create') }}" class="btn btn-success">Add Design Type</a>
                            @endif
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Product Category</th>
                                        <th>Name</th>
                                        <th class="text-end">Sort</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($designTypes as $designType)
                                        <tr>
                                            <td>{{ $designTypes->firstItem() + $loop->index }}</td>
                                            <td>{{ $designType->productCategory?->name }}</td>
                                            <td class="fw-medium">{{ $designType->name }}</td>
                                            <td class="text-end">{{ $designType->sort_order }}</td>
                                            <td>
                                                @if ($designType->isActive())
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                                    @include('admin.partials.edit-icon', ['url' => route('admin.design-types.edit', $designType)])
                                                    @include('admin.partials.status-actions', [
                                                        'record' => $designType,
                                                        'activateUrl' => route('admin.design-types.status', $designType),
                                                    ])
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $designTypes->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
