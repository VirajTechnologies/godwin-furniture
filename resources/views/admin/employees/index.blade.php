@extends('layouts.admin')

@section('title', 'Employees')
@section('page_title', 'Employees')

@section('breadcrumb')
    <li class="breadcrumb-item active">Employees</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Employees</h5>
                    <a href="{{ route('admin.employees.create') }}" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> Add Employee
                    </a>
                </div>
                <div class="card-body">
                    @if ($employees->isEmpty())
                        <div class="text-center py-5">
                            <h5 class="mb-2">No employees yet</h5>
                            <p class="text-muted mb-3">Add a staff login. An employee can also buy later, using the same account.</p>
                            <a href="{{ route('admin.employees.create') }}" class="btn btn-success">Add Employee</a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Warehouse</th>
                                        <th>Branch</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($employees as $employee)
                                        <tr>
                                            <td>{{ $employees->firstItem() + $loop->index }}</td>
                                            <td class="fw-medium">{{ $employee->employee_code }}</td>
                                            <td>{{ $employee->user->name }}</td>
                                            <td>{{ $employee->user->email ?: '—' }}</td>
                                            <td>{{ $employee->user->role?->name ?? '—' }}</td>
                                            <td>{{ $employee->warehouse?->name ?? '—' }}</td>
                                            <td>{{ $employee->branch?->name ?? '—' }}</td>
                                            <td>{{ $employee->user->phone ?: '—' }}</td>
                                            <td>
                                                @if ($employee->isActive())
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                                    @include('admin.partials.edit-icon', ['url' => route('admin.employees.edit', $employee)])
                                                    @include('admin.partials.status-actions', [
                                                        'record' => $employee,
                                                        'activateUrl' => route('admin.employees.status', $employee),
                                                    ])
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $employees->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
