@extends('layouts.agent')

@section('title', $transfer->code)
@section('page_title', $transfer->code)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('agent.transfers.index') }}">Receive</a></li>
    <li class="breadcrumb-item active">{{ $transfer->statusLabel() }}</li>
@endsection

@section('content')
    @php
        $oldItems = collect(old('items', []))->filter(fn ($row) => is_array($row))->keyBy(fn (array $row) => (int) ($row['id'] ?? 0));
    @endphp
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">{{ $transfer->code }} · {{ $transfer->statusLabel() }}</h5>
                    @if ($transfer->isDispatched())
                        <button
                            type="submit"
                            form="complete-transfer-form"
                            class="btn btn-success"
                            onclick="return confirm('Complete this transfer? Accepted stock stays at the shop. Returned stock goes back to the warehouse.')"
                        >Complete</button>
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
                    @if ($transfer->isDispatched())
                        <form method="POST" action="{{ route('agent.transfers.complete', $transfer) }}" id="complete-transfer-form">
                            @csrf
                            @error('items')
                                <div class="text-danger small mb-2">{{ $message }}</div>
                            @enderror
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>SR No.</th>
                                            <th>Code</th>
                                            <th>Product</th>
                                            <th>Dispatched</th>
                                            <th>Accept</th>
                                            <th>Return</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($transfer->items as $index => $item)
                                            @php
                                                $old = $oldItems->get($item->id, []);
                                                $accepted = (int) ($old['received_quantity'] ?? $item->quantity);
                                                $returned = array_key_exists('returned_quantity', $old)
                                                    ? (int) $old['returned_quantity']
                                                    : max($item->quantity - $accepted, 0);
                                            @endphp
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td class="fw-medium">{{ $item->product?->code }}</td>
                                                <td>{{ $item->product?->name }}</td>
                                                <td>{{ $item->quantity }}</td>
                                                <td style="max-width: 140px;">
                                                    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                                    <input
                                                        type="number"
                                                        class="form-control accept-qty @error('items.'.$item->id) is-invalid @enderror"
                                                        name="items[{{ $index }}][received_quantity]"
                                                        value="{{ $accepted }}"
                                                        min="0"
                                                        max="{{ $item->quantity }}"
                                                        data-dispatched="{{ $item->quantity }}"
                                                        required
                                                    >
                                                    @error('items.'.$item->id)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td style="max-width: 140px;">
                                                    <input type="number" class="form-control return-qty" name="items[{{ $index }}][returned_quantity]" value="{{ $returned }}" readonly>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4">
                                <label for="return_reason" class="form-label">Return reason</label>
                                <textarea class="form-control @error('return_reason') is-invalid @enderror" id="return_reason" name="return_reason" rows="2" placeholder="Required when any quantity is returned">{{ old('return_reason') }}</textarea>
                                @error('return_reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </form>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Code</th>
                                        <th>Product</th>
                                        <th>Dispatched</th>
                                        @if ($transfer->isSettled())
                                            <th>Accepted</th>
                                            <th>Returned</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transfer->items as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="fw-medium">{{ $item->product?->code }}</td>
                                            <td>{{ $item->product?->name }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            @if ($transfer->isSettled())
                                                <td>{{ $item->received_quantity }}</td>
                                                <td>{{ $item->returned_quantity }}</td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if ($transfer->return_reason)
                            <div class="mt-4">
                                <label class="form-label">Return reason</label>
                                <textarea class="form-control" rows="2" readonly>{{ $transfer->return_reason }}</textarea>
                            </div>
                        @endif
                    @endif
                    <a href="{{ route('agent.transfers.index') }}" class="btn btn-light mt-4">Back</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@if ($transfer->isDispatched())
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.accept-qty').forEach(function (input) {
                    const sync = function () {
                        const dispatched = Number(input.dataset.dispatched || 0);
                        let accepted = Number(input.value);
                        if (!Number.isFinite(accepted) || accepted < 0) {
                            accepted = 0;
                        }
                        if (accepted > dispatched) {
                            accepted = dispatched;
                        }
                        input.value = String(accepted);
                        const returned = input.closest('tr').querySelector('.return-qty');
                        if (returned) {
                            returned.value = String(dispatched - accepted);
                        }
                    };
                    input.addEventListener('input', sync);
                    sync();
                });
            });
        </script>
    @endpush
@endif
