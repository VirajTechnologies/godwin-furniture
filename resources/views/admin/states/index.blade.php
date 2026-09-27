@extends('layouts.admin')

@section('title', 'States')
@section('page_title', 'States')

@section('breadcrumb')
    <li class="breadcrumb-item active">States</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">States</h5>
                    <a href="{{ route('admin.states.create') }}" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> Add state
                    </a>
                </div>
                <div class="card-body">
                    @if ($states->isEmpty())
                        <div class="text-center py-5">
                            <h5 class="mb-2">No states yet</h5>
                            <p class="text-muted mb-3">Add a state before districts and cities.</p>
                            <a href="{{ route('admin.states.create') }}" class="btn btn-success">Add state</a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>State</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($states as $state)
                                        <tr>
                                            <td>{{ $states->firstItem() + $loop->index }}</td>
                                            <td class="fw-medium">{{ $state->state_name }}</td>
                                            <td>
                                                @if ($state->isActive())
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                                    @include('admin.partials.edit-icon', ['url' => route('admin.states.edit', $state)])
                                                    @include('admin.partials.status-actions', [
                                                        'record' => $state,
                                                        'activateUrl' => route('admin.states.status', $state),
                                                    ])
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $states->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
