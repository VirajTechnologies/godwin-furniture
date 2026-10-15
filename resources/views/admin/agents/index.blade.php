@extends('layouts.admin')

@section('title', 'Agents')
@section('page_title', 'Agents')

@section('breadcrumb')
    <li class="breadcrumb-item active">Agents</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Agents</h5>
                    <a href="{{ route('admin.agents.create') }}" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> Add Agent
                    </a>
                </div>
                <div class="card-body">
                    @if ($agents->isEmpty())
                        <div class="text-center py-5">
                            <h5 class="mb-2">No agents yet</h5>
                            <p class="text-muted mb-3">An agent is an outside shop that sells within the minimum price and MRP you set, and earns a commission.</p>
                            <a href="{{ route('admin.agents.create') }}" class="btn btn-success">Add Agent</a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Code</th>
                                        <th>Shop</th>
                                        <th>City</th>
                                        <th>Login</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($agents as $agent)
                                        <tr>
                                            <td>{{ $agents->firstItem() + $loop->index }}</td>
                                            <td class="fw-medium">{{ $agent->code }}</td>
                                            <td>{{ $agent->name }}</td>
                                            <td>{{ $agent->city?->city_name ?? '—' }}</td>
                                            <td>{{ $agent->user?->email ?? '—' }}</td>
                                            <td>{{ $agent->phone ?? '—' }}</td>
                                            <td>
                                                @if ($agent->isActive())
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                @include('admin.partials.edit-icon', ['url' => route('admin.agents.edit', $agent)])
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $agents->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
