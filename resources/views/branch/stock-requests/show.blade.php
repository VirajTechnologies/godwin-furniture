@extends('layouts.branch')

@section('title', $stockRequest->code)
@section('page_title', $stockRequest->code)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('branch.stock-requests.index') }}">Request</a></li>
    <li class="breadcrumb-item active">{{ $stockRequest->label() }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">{{ $stockRequest->code }} · {{ $stockRequest->label() }}</h5>
                    @if ($stockRequest->isRequested() && ! $stockRequest->stock_transfer_id)
                        <form method="POST" action="{{ route('branch.stock-requests.cancel', $stockRequest) }}" onsubmit="return confirm('Cancel this request?')">
                            @csrf
                            <button type="submit" class="btn btn-soft-danger">Cancel Request</button>
                        </form>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Requested</label>
                            <input type="text" class="form-control" value="{{ $stockRequest->created_at?->format('d M Y, h:i A') }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Transfer</label>
                            <input type="text" class="form-control" value="{{ $stockRequest->transfer?->code ?? '—' }}" readonly>
                        </div>
                        @if ($stockRequest->notes)
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" rows="2" readonly>{{ $stockRequest->notes }}</textarea>
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
                                @foreach ($stockRequest->items as $item)
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
                    <a href="{{ route('branch.stock-requests.index') }}" class="btn btn-light mt-4">Back</a>
                </div>
            </div>
        </div>
    </div>
@endsection
