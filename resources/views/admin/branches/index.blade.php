@extends('layouts.admin')

@section('title', 'Branches')
@section('page_title', 'Branches')

@section('breadcrumb')
    <li class="breadcrumb-item active">Branches</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Branches</h5>
                    <a href="{{ route('admin.branches.create') }}" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> Add Branch
                    </a>
                </div>
                <div class="card-body">
                    @if ($branches->isEmpty())
                        <div class="text-center py-5">
                            <h5 class="mb-2">No branches yet</h5>
                            <p class="text-muted mb-3">A branch sells to customers and is supplied by one warehouse.</p>
                            <a href="{{ route('admin.branches.create') }}" class="btn btn-success">Add Branch</a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Warehouse</th>
                                        <th>State</th>
                                        <th>District</th>
                                        <th>City</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($branches as $branch)
                                        <tr>
                                            <td>{{ $branches->firstItem() + $loop->index }}</td>
                                            <td class="fw-medium">{{ $branch->code }}</td>
                                            <td>{{ $branch->name }}</td>
                                            <td>{{ $branch->warehouse?->name ?? '—' }}</td>
                                            <td>{{ $branch->state?->state_name ?? '—' }}</td>
                                            <td>{{ $branch->district?->district_name ?? '—' }}</td>
                                            <td>{{ $branch->city?->city_name ?? '—' }}</td>
                                            <td>{{ $branch->phone ?: '—' }}</td>
                                            <td>
                                                @if ($branch->isActive())
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                                    @include('admin.partials.edit-icon', ['url' => route('admin.branches.edit', $branch)])
                                                    @include('admin.partials.status-actions', [
                                                        'record' => $branch,
                                                        'activateUrl' => route('admin.branches.status', $branch),
                                                    ])
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $branches->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
