@extends('layouts.admin')

@section('title', 'Finishes')
@section('page_title', 'Masters')

@section('breadcrumb')
    <li class="breadcrumb-item active">Finishes</li>
@endsection

@section('content')
    @include('admin.partials.masters-tabs')

    @php
        $hasFilters = $filters['q'] !== '' || $filters['status'] !== '';
    @endphp

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Finishes</h5>
                    <a href="{{ route('admin.product-finishes.create') }}" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> Add Finish
                    </a>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.product-finishes.index') }}" class="row g-2 mb-4">
                        <div class="col-md-4">
                            <label for="productFinish_filter_q" class="form-label">Search</label>
                            <input type="text" class="form-control" id="productFinish_filter_q" name="q" value="{{ $filters['q'] }}" placeholder="Name">
                        </div>
                        <div class="col-md-3">
                            <label for="productFinish_filter_status" class="form-label">Status</label>
                            <select class="form-select" id="productFinish_filter_status" name="status">
                                <option value="" @selected($filters['status'] === '')>All</option>
                                <option value="active" @selected($filters['status'] === 'active')>Active</option>
                                <option value="inactive" @selected($filters['status'] === 'inactive')>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            @if ($hasFilters)
                                <a href="{{ route('admin.product-finishes.index') }}" class="btn btn-light">Clear</a>
                            @endif
                        </div>
                    </form>

                    @if ($productFinishes->isEmpty())
                        <div class="text-center py-5">
                            @if ($hasFilters)
                                <h5 class="mb-2">No matches</h5>
                                <a href="{{ route('admin.product-finishes.index') }}" class="btn btn-light">Clear Filters</a>
                            @else
                                <h5 class="mb-2">No Finishes yet</h5>
                                <a href="{{ route('admin.product-finishes.create') }}" class="btn btn-success">Add Finish</a>
                            @endif
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Name</th>
                                        <th class="text-end">Sort</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($productFinishes as $item)
                                        <tr>
                                            <td>{{ $productFinishes->firstItem() + $loop->index }}</td>
                                            <td class="fw-medium">{{ $item->name }}</td>
                                            <td class="text-end">{{ $item->sort_order }}</td>
                                            <td>
                                                @if ($item->isActive())
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                                    @include('admin.partials.edit-icon', ['url' => route('admin.product-finishes.edit', $item)])
                                                    @include('admin.partials.status-actions', [
                                                        'record' => $item,
                                                        'activateUrl' => route('admin.product-finishes.status', $item),
                                                    ])
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $productFinishes->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection