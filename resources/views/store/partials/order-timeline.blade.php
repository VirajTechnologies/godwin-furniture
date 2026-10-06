@if ($order->isOnline())
    @php($timeline = $order->fulfilmentTimeline())
    <div class="{{ $compact ?? false ? '' : 'card border-0 shadow-sm rounded-4 p-4 bg-white mb-4' }}">
        @unless ($compact ?? false)
            <h5 class="font-heading fw-bold text-dark pb-3 border-bottom mb-3">
                <i class="fas fa-route text-amber me-2"></i> Order Progress
            </h5>
        @endunless

        <div class="row g-3">
            @foreach ($timeline as $step)
                <div class="col-6 col-md-3">
                    <div class="border rounded-3 p-3 h-100 {{ $step['current'] ? 'border-primary bg-primary-subtle' : ($step['done'] ? 'border-success bg-success-subtle' : 'bg-light') }}">
                        <div class="small text-muted {{ isset($compact) && $compact ? '' : 'font-heading' }} mb-1">{{ $step['label'] }}</div>
                        @if ($step['at'])
                            <div class="fw-semibold {{ isset($compact) && $compact ? '' : 'font-heading' }} text-dark small">
                                {{ $step['at']->format('d M Y') }}
                                <div class="fw-normal text-muted">{{ $step['at']->format('h:i A') }}</div>
                            </div>
                        @else
                            <div class="fw-semibold {{ isset($compact) && $compact ? '' : 'font-heading' }} text-muted small">Pending</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
