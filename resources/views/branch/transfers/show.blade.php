@extends('layouts.branch')

@section('title', $transfer->code)
@section('page_title', $transfer->code)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('branch.transfers.index') }}">Receive</a></li>
    <li class="breadcrumb-item active">{{ $transfer->statusLabel() }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">{{ $transfer->code }} · {{ $transfer->statusLabel() }}</h5>
                    @if ($transfer->isDispatched())
                        <div class="d-flex gap-2">
                            <form method="POST" action="{{ route('branch.transfers.receive', $transfer) }}" onsubmit="return confirm('Receive this transfer? Branch stock will increase.')">
                                @csrf
                                <button type="submit" class="btn btn-success">Receive</button>
                            </form>
                            <button
                                type="submit"
                                form="return-transfer-form"
                                class="btn btn-soft-danger"
                                onclick="return confirm('Return this transfer to the warehouse? Warehouse stock will increase.')"
                            >Return</button>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Warehouse</label>
                            <input type="text" class="form-control" value="{{ $transfer->warehouse?->name }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Dispatched</label>
                            <input type="text" class="form-control" value="{{ $transfer->dispatched_at?->format('d M Y, h:i A') }}" readonly>
                        </div>
                        @if ($transfer->received_at)
                            <div class="col-md-4">
                                <label class="form-label">Received</label>
                                <input type="text" class="form-control" value="{{ $transfer->received_at->format('d M Y, h:i A') }}" readonly>
                            </div>
                        @endif
                        @if ($transfer->returned_at)
                            <div class="col-md-4">
                                <label class="form-label">Returned</label>
                                <input type="text" class="form-control" value="{{ $transfer->returned_at->format('d M Y, h:i A') }}" readonly>
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
                    @if ($transfer->isDispatched())
                        <form method="POST" action="{{ route('branch.transfers.return', $transfer) }}" id="return-transfer-form" class="mt-4">
                            @csrf
                            <label for="return_reason" class="form-label">Return reason</label>
                            <textarea class="form-control @error('return_reason') is-invalid @enderror" id="return_reason" name="return_reason" rows="2" placeholder="Optional — damaged, wrong items, short quantity…">{{ old('return_reason') }}</textarea>
                            @error('return_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </form>
                    @elseif ($transfer->return_reason)
                        <div class="mt-4">
                            <label class="form-label">Return reason</label>
                            <textarea class="form-control" rows="2" readonly>{{ $transfer->return_reason }}</textarea>
                        </div>
                    @endif
                    <a href="{{ route('branch.transfers.index') }}" class="btn btn-light mt-4">Back</a>
                </div>
            </div>
        </div>
    </div>
@endsection
