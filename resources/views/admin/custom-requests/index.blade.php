@extends('layouts.admin')

@section('title', 'Custom Requests')
@section('page_title', 'Inventory')

@section('breadcrumb')
    <li class="breadcrumb-item active">Custom Requests</li>
@endsection

@section('content')
    @include('admin.partials.inventory-tabs')

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Custom Requests</h5></div>
                <div class="card-body">
                    @if ($requests->isEmpty())
                        <div class="text-center py-5">
                            <h5 class="mb-2">No custom requests yet</h5>
                            <p class="text-muted mb-0">Agents send catalog customizations to the primary branch. They appear here for head office too.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Code</th>
                                        <th>Agent</th>
                                        <th>Branch</th>
                                        <th>Product</th>
                                        <th>Status</th>
                                        <th>Requested</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($requests as $customRequest)
                                        <tr>
                                            <td>{{ $requests->firstItem() + $loop->index }}</td>
                                            <td class="fw-medium">{{ $customRequest->code }}</td>
                                            <td>{{ $customRequest->agent?->name ?? '—' }}</td>
                                            <td>{{ $customRequest->branch?->name ?? '—' }}</td>
                                            <td>{{ $customRequest->displayName() }}</td>
                                            <td>{{ $customRequest->label() }}</td>
                                            <td>{{ $customRequest->created_at?->format('d M Y, h:i A') }}</td>
                                            <td>
                                                <a href="{{ route('admin.custom-requests.show', $customRequest) }}" class="btn btn-sm btn-soft-primary btn-icon" title="View">
                                                    <i class="ri-eye-fill"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $requests->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
