@extends('layouts.branch')

@section('title', 'Transfer Report')
@section('page_title', 'Transfer Report')

@section('breadcrumb')
    <li class="breadcrumb-item active">Transfer Report</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Transfers</h5></div>
                <div class="card-body">
                    @include('branch.reports._dates')
                    @if ($transfers->isEmpty())
                        <p class="text-muted mb-0">No transfers in this period.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Code</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Dispatched</th>
                                        <th>Received</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transfers as $transfer)
                                        <tr>
                                            <td>{{ $transfers->firstItem() + $loop->index }}</td>
                                            <td class="fw-medium">
                                                @if ($transfer->isDispatched() || $transfer->isReceived())
                                                    <a href="{{ route('branch.transfers.show', $transfer) }}">{{ $transfer->code }}</a>
                                                @else
                                                    {{ $transfer->code }}
                                                @endif
                                            </td>
                                            <td>{{ $transfer->statusLabel() }}</td>
                                            <td>{{ $transfer->created_at?->format('d M Y, h:i A') }}</td>
                                            <td>{{ $transfer->dispatched_at?->format('d M Y, h:i A') ?? '—' }}</td>
                                            <td>{{ $transfer->received_at?->format('d M Y, h:i A') ?? '—' }}</td>
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
