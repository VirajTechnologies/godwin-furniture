@extends('layouts.branch')

@section('title', 'Receive')
@section('page_title', 'Receive')

@section('breadcrumb')
    <li class="breadcrumb-item active">Receive</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Transfers For This Branch</h5></div>
                <div class="card-body">
                    @if ($transfers->isEmpty())
                        <div class="text-center py-5">
                            <h5 class="mb-2">No transfers to receive</h5>
                            <p class="text-muted mb-0">Stock increases here after you count a dispatched transfer and receive it.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Code</th>
                                        <th>Warehouse</th>
                                        <th>Status</th>
                                        <th>Dispatched</th>
                                        <th>Received</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transfers as $transfer)
                                        <tr>
                                            <td>{{ $transfers->firstItem() + $loop->index }}</td>
                                            <td class="fw-medium"><a href="{{ route('branch.transfers.show', $transfer) }}">{{ $transfer->code }}</a></td>
                                            <td>{{ $transfer->warehouse?->name ?? '—' }}</td>
                                            <td>
                                                @if ($transfer->isReceived())
                                                    <span class="badge bg-success">Received</span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning">Dispatched</span>
                                                @endif
                                            </td>
                                            <td>{{ $transfer->dispatched_at?->format('d M Y, h:i A') ?? '—' }}</td>
                                            <td>{{ $transfer->received_at?->format('d M Y, h:i A') ?? '—' }}</td>
                                            <td>
                                                <a href="{{ route('branch.transfers.show', $transfer) }}" class="btn btn-sm btn-soft-primary btn-icon" title="View" aria-label="View">
                                                    <i class="ri-eye-fill"></i>
                                                </a>
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
