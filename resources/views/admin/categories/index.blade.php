@extends('layouts.admin')

@section('title', 'Categories')
@section('page_title', 'Categories')

@section('breadcrumb')
    <li class="breadcrumb-item active">Categories</li>
@endsection

@section('content')
    @php
        $hasFilters = $filters['q'] !== '' || $filters['type'] !== '' || $filters['status'] !== '' || $filters['room_id'];
    @endphp

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center flex-wrap gap-2">
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-1">Categories</h5>
                        <p class="text-muted mb-0 small">Categories hold subcategories. Products belong to subcategories.</p>
                    </div>
                    <a href="{{ route('admin.categories.create') }}" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> Add Category
                    </a>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.categories.index') }}" class="row g-2 mb-4">
                        <div class="col-md-3">
                            <label for="q" class="form-label">Search</label>
                            <input type="text" class="form-control" id="q" name="q" value="{{ $filters['q'] }}" placeholder="Category or subcategory name">
                        </div>
                        <div class="col-md-2">
                            <label for="type" class="form-label">Type</label>
                            <select class="form-select" id="type" name="type">
                                <option value="" @selected($filters['type'] === '')>All</option>
                                <option value="room" @selected($filters['type'] === 'room')>Categories</option>
                                <option value="subcategory" @selected($filters['type'] === 'subcategory')>Subcategories</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="category_filter_status" class="form-label">Status</label>
                            <select class="form-select" id="category_filter_status" name="status">
                                <option value="" @selected($filters['status'] === '')>All</option>
                                <option value="active" @selected($filters['status'] === 'active')>Active</option>
                                <option value="inactive" @selected($filters['status'] === 'inactive')>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="room_id" class="form-label">Parent Category</label>
                            <select class="form-select" id="room_id" name="room_id">
                                <option value="">All categories</option>
                                @foreach ($filterRooms as $room)
                                    <option value="{{ $room->id }}" @selected((string) $filters['room_id'] === (string) $room->id)>{{ $room->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            @if ($hasFilters)
                                <a href="{{ route('admin.categories.index') }}" class="btn btn-light">Clear</a>
                            @endif
                        </div>
                    </form>

                    @if ($rooms->isEmpty())
                        <div class="text-center py-5">
                            @if ($hasFilters)
                                <h5 class="mb-2">No categories match</h5>
                                <p class="text-muted mb-3">Try clearing the filters or searching a different name.</p>
                                <a href="{{ route('admin.categories.index') }}" class="btn btn-light">Clear Filters</a>
                            @else
                                <h5 class="mb-2">No categories yet</h5>
                                <p class="text-muted mb-3">Add a category such as Living Room, then add subcategories under it.</p>
                                <a href="{{ route('admin.categories.create') }}" class="btn btn-success">Add Category</a>
                            @endif
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Menu Group</th>
                                        <th class="text-end">Sort Order</th>
                                        <th class="text-end">Products</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rooms as $category)
                                        <tr class="table-light">
                                            <td class="fw-semibold">{{ $category->name }}</td>
                                            <td><span class="badge bg-primary-subtle text-primary">Category</span></td>
                                            <td class="text-muted">—</td>
                                            <td class="text-end">{{ $category->sort_order }}</td>
                                            <td class="text-end text-muted">—</td>
                                            <td>
                                                @if ($category->isActive())
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                                    @include('admin.partials.edit-icon', ['url' => route('admin.categories.edit', $category)])
                                                    <a href="{{ route('admin.categories.create', ['parent_id' => $category->id]) }}" class="btn btn-sm btn-soft-success" title="Add subcategory under this category">
                                                        <i class="ri-add-line align-bottom"></i> Add Subcategory
                                                    </a>
                                                    @include('admin.partials.status-actions', [
                                                        'record' => $category,
                                                        'activateUrl' => route('admin.categories.status', $category),
                                                    ])
                                                </div>
                                            </td>
                                        </tr>

                                        @forelse ($category->children as $child)
                                            <tr>
                                                <td>
                                                    <span class="text-muted me-2">└</span>{{ $child->name }}
                                                </td>
                                                <td><span class="badge bg-secondary-subtle text-secondary">Subcategory</span></td>
                                                <td>{{ $child->menu_group ?: '—' }}</td>
                                                <td class="text-end">{{ $child->sort_order }}</td>
                                                <td class="text-end">{{ $child->products_count }}</td>
                                                <td>
                                                    @if ($child->isActive())
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-wrap gap-2 align-items-center">
                                                        @include('admin.partials.edit-icon', ['url' => route('admin.categories.edit', $child)])
                                                        @include('admin.partials.status-actions', [
                                                            'record' => $child,
                                                            'activateUrl' => route('admin.categories.status', $child),
                                                        ])
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            @if ($filters['type'] !== 'room')
                                                <tr>
                                                    <td colspan="7" class="text-muted small">
                                                        <span class="text-muted me-2">└</span>No subcategories{{ $hasFilters ? ' match these filters' : ' yet' }}.
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforelse
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $rooms->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
