@if ($order->isOnline())
    @php($timeline = $order->fulfilmentTimeline())
    <div class="card mb-3">
        <div class="card-header"><h5 class="card-title mb-0">Order Progress</h5></div>
        <div class="card-body">
            <div class="row g-3">
                @foreach ($timeline as $step)
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-3 h-100 {{ $step['current'] ? 'border-primary bg-primary-subtle' : ($step['done'] ? 'border-success bg-success-subtle' : 'bg-light') }}">
                            <div class="text-muted fs-12 mb-1">{{ $step['label'] }}</div>
                            @if ($step['at'])
                                <div class="fw-semibold">
                                    {{ $step['at']->format('d M Y') }}
                                    <div class="fw-normal text-muted fs-12">{{ $step['at']->format('h:i A') }}</div>
                                </div>
                            @else
                                <div class="fw-semibold text-muted">Pending</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
