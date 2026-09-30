@extends('layouts.admin')

@section('title', 'Stock Requests')
@section('page_title', 'Stock Requests')

@section('breadcrumb')
    <li class="breadcrumb-item active">Stock Requests</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Stock Requests</h5></div>
                <div class="card-body">
                    @if ($requests->isEmpty())
                        <div class="text-center py-5">
                            <h5 class="mb-2">No stock requests yet</h5>
                            <p class="text-muted mb-0">A branch manager asks for stock from here. Creating a transfer from the request is what sends the pieces.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Code</th>
                                        <th>Branch</th>
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
                                            <td class="fw-medium"><a href="{{ route('admin.stock-requests.show', $stockRequest) }}">{{ $stockRequest->code }}</a></td>
                                            <td>{{ $stockRequest->branch?->name ?? '—' }}</td>
                                            <td>{{ $stockRequest->label() }}</td>
                                            <td>
                                                @if ($stockRequest->transfer)
                                                    <a href="{{ route('admin.transfers.edit', $stockRequest->transfer) }}">{{ $stockRequest->transfer->code }}</a>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>{{ $stockRequest->created_at?->format('d M Y, h:i A') }}</td>
                                            <td>
                                                <a href="{{ route('admin.stock-requests.show', $stockRequest) }}" class="btn btn-sm btn-soft-primary btn-icon" title="View" aria-label="View">
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
