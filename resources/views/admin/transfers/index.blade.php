@extends('layouts.admin')

@section('title', 'Transfers')
@section('page_title', 'Transfers')

@section('breadcrumb')
    <li class="breadcrumb-item active">Transfers</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Transfers</h5>
                    <a href="{{ route('admin.transfers.create') }}" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> Add Transfer
                    </a>
                </div>
                <div class="card-body">
                    @if ($transfers->isEmpty())
                        <div class="text-center py-5">
                            <h5 class="mb-2">No transfers yet</h5>
                            <p class="text-muted mb-3">Send stock from a warehouse to the branch it supplies. The branch quantity increases only when the transfer is received.</p>
                            <a href="{{ route('admin.transfers.create') }}" class="btn btn-success">Add Transfer</a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Code</th>
                                        <th>Branch</th>
                                        <th>Warehouse</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Dispatched</th>
                                        <th>Received</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transfers as $transfer)
                                        <tr>
                                            <td>{{ $transfers->firstItem() + $loop->index }}</td>
                                            <td class="fw-medium"><a href="{{ route('admin.transfers.edit', $transfer) }}">{{ $transfer->code }}</a></td>
                                            <td>{{ $transfer->branch?->name ?? '—' }}</td>
                                            <td>{{ $transfer->warehouse?->name ?? '—' }}</td>
                                            <td>
                                                @if ($transfer->status === 'received')
                                                    <span class="badge bg-success">Received</span>
                                                @elseif ($transfer->status === 'dispatched')
                                                    <span class="badge bg-warning-subtle text-warning">Dispatched</span>
                                                @elseif ($transfer->status === 'cancelled')
                                                    <span class="badge bg-danger-subtle text-danger">Cancelled</span>
                                                    <div class="text-muted small mt-1">{{ $transfer->cancelled_at?->format('d M Y, h:i A') }}</div>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary">Draft</span>
                                                @endif
                                            </td>
                                            <td>{{ $transfer->created_at?->format('d M Y, h:i A') }}</td>
                                            <td>{{ $transfer->dispatched_at?->format('d M Y, h:i A') ?? '—' }}</td>
                                            <td>{{ $transfer->received_at?->format('d M Y, h:i A') ?? '—' }}</td>
                                            <td>
                                                @include('admin.partials.edit-icon', ['url' => route('admin.transfers.edit', $transfer)])
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $transfers->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
