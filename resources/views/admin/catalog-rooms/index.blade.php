@extends('layouts.admin')

@section('title', 'Rooms')
@section('page_title', 'Rooms')

@section('breadcrumb')
    <li class="breadcrumb-item active">Rooms</li>
@endsection

@section('content')
    @php
        $hasFilters = $filters['q'] !== ''
            || $filters['status'] !== ''
            || $filters['project_category_id'] !== ''
            || $filters['show_on_store'] !== '';
    @endphp

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Rooms</h5>
                    <a href="{{ route('admin.catalog-rooms.create') }}" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> Add Room
                    </a>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.catalog-rooms.index') }}" class="row g-2 mb-4">
                        <div class="col-md-3">
                            <label for="q" class="form-label">Search</label>
                            <input type="text" class="form-control" id="q" name="q" value="{{ $filters['q'] }}" placeholder="Name">
                        </div>
                        <div class="col-md-3">
                            <label for="project_category_id" class="form-label">Project</label>
                            <select class="form-select" id="project_category_id" name="project_category_id">
                                <option value="" @selected($filters['project_category_id'] === '')>All</option>
                                @foreach ($filterProjects as $project)
                                    <option value="{{ $project->id }}" @selected((string) $filters['project_category_id'] === (string) $project->id)>{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="show_on_store" class="form-label">Store</label>
                            <select class="form-select" id="show_on_store" name="show_on_store">
                                <option value="" @selected($filters['show_on_store'] === '')>All</option>
                                <option value="1" @selected($filters['show_on_store'] === '1')>Ecommerce</option>
                                <option value="0" @selected($filters['show_on_store'] === '0')>Admin only</option>
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
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            @if ($hasFilters)
                                <a href="{{ route('admin.catalog-rooms.index') }}" class="btn btn-light">Clear</a>
                            @endif
                        </div>
                    </form>

                    @if ($catalogRooms->isEmpty())
                        <div class="text-center py-5">
                            @if ($hasFilters)
                                <h5 class="mb-2">No rooms match</h5>
                                <p class="text-muted mb-3">Try clearing the filters.</p>
                                <a href="{{ route('admin.catalog-rooms.index') }}" class="btn btn-light">Clear Filters</a>
                            @else
                                <h5 class="mb-2">No Rooms yet</h5>
                                <a href="{{ route('admin.catalog-rooms.create') }}" class="btn btn-success">Add Room</a>
                            @endif
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Project</th>
                                        <th>Name</th>
                                        <th>Store</th>
                                        <th class="text-end">Sort</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($catalogRooms as $catalogRoom)
                                        <tr>
                                            <td>{{ $catalogRooms->firstItem() + $loop->index }}</td>
                                            <td>{{ $catalogRoom->projectCategory?->name }}</td>
                                            <td class="fw-medium">{{ $catalogRoom->name }}</td>
                                            <td>
                                                @if ($catalogRoom->show_on_store)
                                                    <span class="badge bg-success-subtle text-success">Ecommerce</span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary">Admin only</span>
                                                @endif
                                            </td>
                                            <td class="text-end">{{ $catalogRoom->sort_order }}</td>
                                            <td>
                                                @if ($catalogRoom->isActive())
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                                    @include('admin.partials.edit-icon', ['url' => route('admin.catalog-rooms.edit', $catalogRoom)])
                                                    @include('admin.partials.status-actions', [
                                                        'record' => $catalogRoom,
                                                        'activateUrl' => route('admin.catalog-rooms.status', $catalogRoom),
                                                    ])
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $catalogRooms->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
