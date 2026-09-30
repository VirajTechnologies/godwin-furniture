@extends('layouts.branch')

@section('title', 'Request')
@section('page_title', 'Request')

@section('breadcrumb')
    <li class="breadcrumb-item active">Request</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Stock Requests</h5>
                    <a href="{{ route('branch.stock-requests.create') }}" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> New Request
                    </a>
                </div>
                <div class="card-body">
                    @if ($requests->isEmpty())
                        <div class="text-center py-5">
                            <h5 class="mb-2">No stock requests yet</h5>
                            <p class="text-muted mb-3">Ask head office for pieces. A request does not change the quantity at this branch.</p>
                            <a href="{{ route('branch.stock-requests.create') }}" class="btn btn-success">New Request</a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Code</th>
                                        <th>Status</th>
                                        <th>Transfer</th>
                                        <th>Requested</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($requests as $stockRequest)
                                        <tr>
                                            <td>{{ $requests->firstItem() + $loop->index }}</td>
                                            <td class="fw-medium"><a href="{{ route('branch.stock-requests.show', $stockRequest) }}">{{ $stockRequest->code }}</a></td>
                                            <td>{{ $stockRequest->label() }}</td>
                                            <td>
                                                @if ($stockRequest->transfer)
                                                    {{ $stockRequest->transfer->code }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>{{ $stockRequest->created_at?->format('d M Y, h:i A') }}</td>
                                            <td>
                                                <a href="{{ route('branch.stock-requests.show', $stockRequest) }}" class="btn btn-sm btn-soft-primary btn-icon" title="View" aria-label="View">
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
