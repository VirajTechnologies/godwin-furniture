@extends('layouts.admin')

@section('title', $stockRequest->code)
@section('page_title', $stockRequest->code)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.stock-requests.index') }}">Stock Requests</a></li>
    <li class="breadcrumb-item active">{{ $stockRequest->code }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">{{ $stockRequest->code }} · {{ $stockRequest->label() }}</h5>
                    @if ($stockRequest->isRequested() && (! $stockRequest->transfer || $stockRequest->transfer->isCancelled()))
                        <form method="POST" action="{{ route('admin.stock-requests.transfer', $stockRequest) }}">
                            @csrf
                            <button type="submit" class="btn btn-success">Create Transfer</button>
                        </form>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Branch</label>
                            <input type="text" class="form-control" value="{{ $stockRequest->branch?->name }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Supplying Warehouse</label>
                            <input type="text" class="form-control" value="{{ $stockRequest->branch?->warehouse?->name }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Requested</label>
                            <input type="text" class="form-control" value="{{ $stockRequest->created_at?->format('d M Y, h:i A') }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Transfer</label>
                            @if ($stockRequest->transfer)
                                <div class="form-control">
                                    <a href="{{ route('admin.transfers.edit', $stockRequest->transfer) }}">{{ $stockRequest->transfer->code }}</a>
                                </div>
                            @else
                                <input type="text" class="form-control" value="—" readonly>
                            @endif
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
                    <a href="{{ route('admin.stock-requests.index') }}" class="btn btn-light mt-4">Back</a>
                </div>
            </div>
        </div>
    </div>
@endsection
