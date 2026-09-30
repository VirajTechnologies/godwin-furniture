@extends('layouts.admin')

@section('title', 'Edit Transfer')
@section('page_title', $transfer->code)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.transfers.index') }}">Transfers</a></li>
    <li class="breadcrumb-item active">{{ $transfer->statusLabel() }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">{{ $transfer->code }} · {{ $transfer->statusLabel() }}</h5>
                    @if ($transfer->isDraft())
                        <form method="POST" action="{{ route('admin.transfers.dispatch', $transfer) }}" onsubmit="return confirm('Dispatch this transfer? Warehouse stock will decrease.')">
                            @csrf
                            <button type="submit" class="btn btn-warning">Dispatch</button>
                        </form>
                    @endif
                </div>
                <div class="card-body">
                    @if ($transfer->isDraft())
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Created</label>
                                <input type="text" class="form-control" value="{{ $transfer->created_at?->format('d M Y, h:i A') }}" readonly>
                            </div>
                        </div>
                        @include('admin.transfers._form')
                        <form method="POST" action="{{ route('admin.transfers.cancel', $transfer) }}" class="mt-3" onsubmit="return confirm('Cancel this draft?')">
                            @csrf
                            <button type="submit" class="btn btn-soft-danger">Cancel Transfer</button>
                        </form>
                    @else
                        @if ($transfer->isDispatched())
                            <p class="text-muted">The branch manager receives this transfer after counting the pieces at the showroom.</p>
                        @endif
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Branch</label>
                                <input type="text" class="form-control" value="{{ $transfer->branch?->name }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Supplying Warehouse</label>
                                <input type="text" class="form-control" value="{{ $transfer->warehouse?->name }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <input type="text" class="form-control" value="{{ $transfer->statusLabel() }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Created</label>
                                <input type="text" class="form-control" value="{{ $transfer->created_at?->format('d M Y, h:i A') }}" readonly>
                            </div>
                            @if ($transfer->dispatched_at)
                                <div class="col-md-4">
                                    <label class="form-label">Dispatched</label>
                                    <input type="text" class="form-control" value="{{ $transfer->dispatched_at->format('d M Y, h:i A') }}" readonly>
                                </div>
                            @endif
                            @if ($transfer->received_at)
                                <div class="col-md-4">
                                    <label class="form-label">Received</label>
                                    <input type="text" class="form-control" value="{{ $transfer->received_at->format('d M Y, h:i A') }}" readonly>
                                </div>
                            @endif
                            @if ($transfer->cancelled_at)
                                <div class="col-md-4">
                                    <label class="form-label">Cancelled</label>
                                    <input type="text" class="form-control" value="{{ $transfer->cancelled_at->format('d M Y, h:i A') }}" readonly>
                                </div>
                            @endif
                            @if ($transfer->notes)
                                <div class="col-12">
                                    <label class="form-label">Notes</label>
                                    <textarea class="form-control" rows="2" readonly>{{ $transfer->notes }}</textarea>
                                </div>
                            @endif
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Code</th>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transfer->items as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="fw-medium">{{ $item->product?->code }}</td>
                                            <td>{{ $item->product?->name }}</td>
                                            <td>{{ $item->quantity }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <a href="{{ route('admin.transfers.index') }}" class="btn btn-light mt-4">Back</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
