@extends('layouts.agent')

@section('title', 'Custom Requests')
@section('page_title', 'Custom Requests')

@section('breadcrumb')
    <li class="breadcrumb-item active">Custom Requests</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Custom Requests</h5>
                    <a href="{{ route('agent.custom-requests.create') }}" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> New Request
                    </a>
                </div>
                <div class="card-body">
                    @if ($requests->isEmpty())
                        <div class="text-center py-5">
                            <h5 class="mb-2">No custom requests yet</h5>
                            <p class="text-muted mb-3">Send a catalog product with customization notes to the primary branch.</p>
                            <a href="{{ route('agent.custom-requests.create') }}" class="btn btn-success">New Request</a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Product</th>
                                        <th>Branch</th>
                                        <th>Status</th>
                                        <th>Requested</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($requests as $customRequest)
                                        <tr>
                                            <td class="fw-medium">
                                                <a href="{{ route('agent.custom-requests.show', $customRequest) }}">{{ $customRequest->code }}</a>
                                            </td>
                                            <td>{{ $customRequest->displayName() }}</td>
                                            <td>{{ $customRequest->branch?->name ?? '—' }}</td>
                                            <td>{{ $customRequest->label() }}</td>
                                            <td>{{ $customRequest->created_at?->format('d M Y, h:i A') }}</td>
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
