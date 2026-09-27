@extends('layouts.admin')

@section('title', 'Cities')
@section('page_title', 'Cities')

@section('breadcrumb')
    <li class="breadcrumb-item active">Cities</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Cities</h5>
                    <a href="{{ route('admin.cities.create') }}" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> Add city
                    </a>
                </div>
                <div class="card-body">
                    @if ($cities->isEmpty())
                        <div class="text-center py-5">
                            <h5 class="mb-2">No cities yet</h5>
                            <p class="text-muted mb-3">Add a city under a district.</p>
                            <a href="{{ route('admin.cities.create') }}" class="btn btn-success">Add city</a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>City</th>
                                        <th>District</th>
                                        <th>State</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cities as $city)
                                        <tr>
                                            <td>{{ $cities->firstItem() + $loop->index }}</td>
                                            <td class="fw-medium">{{ $city->city_name }}</td>
                                            <td>{{ $city->district->district_name }}</td>
                                            <td>{{ $city->state->state_name }}</td>
                                            <td>
                                                @if ($city->isActive())
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                                    @include('admin.partials.edit-icon', ['url' => route('admin.cities.edit', $city)])
                                                    @include('admin.partials.status-actions', [
                                                        'record' => $city,
                                                        'activateUrl' => route('admin.cities.status', $city),
                                                    ])
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $cities->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
