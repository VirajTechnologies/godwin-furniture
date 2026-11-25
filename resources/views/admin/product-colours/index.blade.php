@extends('layouts.admin')

@section('title', 'Colours')
@section('page_title', 'Colours')

@section('breadcrumb')
    <li class="breadcrumb-item active">Colours</li>
@endsection

@section('content')
    @php
        $hasFilters = $filters['q'] !== '' || $filters['status'] !== '';
    @endphp

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Colours</h5>
                    <a href="{{ route('admin.product-colours.create') }}" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> Add Colour
                    </a>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.product-colours.index') }}" class="row g-2 mb-4">
                        <div class="col-md-4">
                            <label for="productColour_filter_q" class="form-label">Search</label>
                            <input type="text" class="form-control" id="productColour_filter_q" name="q" value="{{ $filters['q'] }}" placeholder="Name">
                        </div>
                        <div class="col-md-3">
                            <label for="productColour_filter_status" class="form-label">Status</label>
                            <select class="form-select" id="productColour_filter_status" name="status">
                                <option value="" @selected($filters['status'] === '')>All</option>
                                <option value="active" @selected($filters['status'] === 'active')>Active</option>
                                <option value="inactive" @selected($filters['status'] === 'inactive')>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            @if ($hasFilters)
                                <a href="{{ route('admin.product-colours.index') }}" class="btn btn-light">Clear</a>
                            @endif
                        </div>
                    </form>

                    @if ($productColours->isEmpty())
                        <div class="text-center py-5">
                            @if ($hasFilters)
                                <h5 class="mb-2">No matches</h5>
                                <a href="{{ route('admin.product-colours.index') }}" class="btn btn-light">Clear Filters</a>
                            @else
                                <h5 class="mb-2">No Colours yet</h5>
                                <a href="{{ route('admin.product-colours.create') }}" class="btn btn-success">Add Colour</a>
                            @endif
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Name</th>
                                        <th>Hex</th>
                                        <th class="text-end">Sort</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($productColours as $item)
                                        <tr>
                                            <td>{{ $productColours->firstItem() + $loop->index }}</td>
                                            <td class="fw-medium">{{ $item->name }}</td>
                                            <td><span class="d-inline-block rounded border" style="width:18px;height:18px;background:{{ $item->hex }};vertical-align:middle"></span> <code>{{ $item->hex }}</code></td>
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
                                                    @include('admin.partials.edit-icon', ['url' => route('admin.product-colours.edit', $item)])
                                                    @include('admin.partials.status-actions', [
                                                        'record' => $item,
                                                        'activateUrl' => route('admin.product-colours.status', $item),
                                                    ])
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $productColours->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection