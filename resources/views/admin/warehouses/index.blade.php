@extends('layouts.admin')

@section('title', 'Warehouses')
@section('page_title', 'Warehouses')

@section('breadcrumb')
    <li class="breadcrumb-item active">Warehouses</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Warehouses</h5>
                    <a href="{{ route('admin.warehouses.create') }}" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> Add warehouse
                    </a>
                </div>
                <div class="card-body">
                    @if ($warehouses->isEmpty())
                        <div class="text-center py-5">
                            <h5 class="mb-2">No warehouses yet</h5>
                            <p class="text-muted mb-3">Add the main warehouse. It becomes the primary warehouse. More warehouses can be added later.</p>
                            <a href="{{ route('admin.warehouses.create') }}" class="btn btn-success">Add warehouse</a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-nowrap align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>State</th>
                                        <th>District</th>
                                        <th>City</th>
                                        <th>Phone</th>
                                        <th>Primary</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($warehouses as $warehouse)
                                        <tr>
                                            <td>{{ $warehouses->firstItem() + $loop->index }}</td>
                                            <td class="fw-medium">{{ $warehouse->code }}</td>
                                            <td>{{ $warehouse->name }}</td>
                                            <td>{{ $warehouse->state?->state_name ?? '—' }}</td>
                                            <td>{{ $warehouse->district?->district_name ?? '—' }}</td>
                                            <td>{{ $warehouse->city?->city_name ?? '—' }}</td>
                                            <td>{{ $warehouse->phone ?: '—' }}</td>
                                            <td>
                                                @if ($warehouse->is_primary)
                                                    <span class="badge bg-success-subtle text-success">Primary</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($warehouse->isActive())
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                                    @include('admin.partials.edit-icon', ['url' => route('admin.warehouses.edit', $warehouse)])
                                                    @if (! $warehouse->is_primary && $warehouse->isActive())
                                                        <form method="POST" action="{{ route('admin.warehouses.primary', $warehouse) }}">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-soft-primary">Set Primary</button>
                                                        </form>
                                                    @endif
                                                    @if (! $warehouse->isActive())
                                                        <form method="POST" action="{{ route('admin.warehouses.status', $warehouse) }}">
                                                            @csrf
                                                            <input type="hidden" name="status" value="active">
                                                            <button type="submit" class="btn btn-sm btn-soft-success">Activate</button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $warehouses->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
