@if ($order->isOnline())
    @php($timeline = $order->fulfilmentTimeline())
    <div class="{{ $compact ?? false ? '' : 'gw-panel mb-4' }}">
        @unless ($compact ?? false)
            <h2 class="gw-panel-title">
                <span><i class="fas fa-route me-2"></i>Order Progress</span>
            </h2>
        @endunless

        <div class="row g-3">
            @foreach ($timeline as $step)
                <div class="col-6 col-md-3">
                    <div class="gw-timeline-step {{ $step['current'] ? 'is-current' : ($step['done'] ? 'is-done' : '') }}">
                        <div class="gw-timeline-label">{{ $step['label'] }}</div>
                        @if ($step['at'])
                            <div class="fw-semibold small" style="color: var(--ink);">
                                {{ $step['at']->format('d M Y') }}
                                <div class="fw-normal" style="color: var(--mut);">{{ $step['at']->format('h:i A') }}</div>
                            </div>
                        @else
                            <div class="small" style="color: var(--mut);">Pending</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
