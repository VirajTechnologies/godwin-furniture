@extends('layouts.admin')

@section('title', 'Districts')
@section('page_title', 'Districts')

@section('breadcrumb')
    <li class="breadcrumb-item active">Districts</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Districts</h5>
                    <a href="{{ route('admin.districts.create') }}" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> Add district
                    </a>
                </div>
                <div class="card-body">
                    @if ($districts->isEmpty())
                        <div class="text-center py-5">
                            <h5 class="mb-2">No districts yet</h5>
                            <p class="text-muted mb-3">Add a district under a state.</p>
                            <a href="{{ route('admin.districts.create') }}" class="btn btn-success">Add district</a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>District</th>
                                        <th>State</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($districts as $district)
                                        <tr>
                                            <td>{{ $districts->firstItem() + $loop->index }}</td>
                                            <td class="fw-medium">{{ $district->district_name }}</td>
                                            <td>{{ $district->state->state_name }}</td>
                                            <td>
                                                @if ($district->isActive())
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                                    @include('admin.partials.edit-icon', ['url' => route('admin.districts.edit', $district)])
                                                    @include('admin.partials.status-actions', [
                                                        'record' => $district,
                                                        'activateUrl' => route('admin.districts.status', $district),
                                                    ])
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $districts->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
