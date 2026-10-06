<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">{{ $order->code }}</h5></div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Customer</label>
                        <input type="text" class="form-control" value="{{ $order->customer?->name }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" value="{{ $order->customer?->phone }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Payment Method</label>
                        <input type="text" class="form-control" value="{{ $order->payment?->methodLabel() }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="text" class="form-control" value="{{ $order->created_at?->format('d M Y, h:i A') }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Total</label>
                        <input type="text" class="form-control" value="₹{{ number_format((float) $order->total, 2) }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fulfilment</label>
                        <input type="text" class="form-control" value="{{ $order->deliveryTypeLabel() }}" readonly>
                    </div>
                    @if ($order->isDoorDelivery() || $order->shippingSummary())
                        <div class="col-md-6">
                            <label class="form-label">Delivery Address</label>
                            <input type="text" class="form-control" value="{{ $order->shippingSummary() ?? '—' }}" readonly>
                        </div>
                    @endif
                    @if ($order->channel === \App\Models\Order::CHANNEL_ONLINE)
                        <div class="col-md-3">
                            <label class="form-label">Channel</label>
                            <input type="text" class="form-control" value="Online" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <input type="text" class="form-control" value="{{ $order->statusLabel() }}" readonly>
                        </div>
                        @if ($order->warehouse)
                            <div class="col-md-3">
                                <label class="form-label">Warehouse</label>
                                <input type="text" class="form-control" value="{{ $order->warehouse->name }}" readonly>
                            </div>
                        @endif
                    @endif
                    @if ($order->notes)
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <input type="text" class="form-control" value="{{ $order->notes }}" readonly>
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
                                <th>Price</th>
                                <th>Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="fw-medium">{{ $item->product?->code }}</td>
                                    <td>{{ $item->product?->name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>₹{{ number_format((float) $item->unit_price, 2) }}</td>
                                    <td>₹{{ number_format((float) $item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
