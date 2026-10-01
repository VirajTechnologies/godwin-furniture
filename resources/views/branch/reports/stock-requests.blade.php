@extends('layouts.branch')

@section('title', 'Stock Request Report')
@section('page_title', 'Stock Request Report')

@section('breadcrumb')
    <li class="breadcrumb-item active">Stock Request Report</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Stock Requests</h5></div>
                <div class="card-body">
                    @include('branch.reports._dates')
                    @if ($requests->isEmpty())
                        <p class="text-muted mb-0">No stock requests in this period.</p>
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
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($requests as $stockRequest)
                                        <tr>
                                            <td>{{ $requests->firstItem() + $loop->index }}</td>
                                            <td class="fw-medium"><a href="{{ route('branch.stock-requests.show', $stockRequest) }}">{{ $stockRequest->code }}</a></td>
                                            <td>{{ $stockRequest->label() }}</td>
                                            <td>
                                                @if ($stockRequest->transfer && ($stockRequest->transfer->isDispatched() || $stockRequest->transfer->isReceived()))
                                                    <a href="{{ route('branch.transfers.show', $stockRequest->transfer) }}">{{ $stockRequest->transfer->code }}</a>
                                                @elseif ($stockRequest->transfer)
                                                    {{ $stockRequest->transfer->code }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>{{ $stockRequest->created_at?->format('d M Y, h:i A') }}</td>
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
