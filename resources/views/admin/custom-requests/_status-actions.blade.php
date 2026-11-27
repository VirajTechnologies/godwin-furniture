@php
    use App\Models\CustomRequest;

    $flow = [
        CustomRequest::STATUS_REQUESTED => 'Requested',
        CustomRequest::STATUS_ACCEPTED => 'Accepted',
        CustomRequest::STATUS_IN_PROGRESS => 'In progress',
        CustomRequest::STATUS_COMPLETED => 'Completed',
    ];

    $badgeClass = match ($customRequest->status) {
        CustomRequest::STATUS_REQUESTED => 'bg-warning-subtle text-warning',
        CustomRequest::STATUS_ACCEPTED => 'bg-info-subtle text-info',
        CustomRequest::STATUS_IN_PROGRESS => 'bg-primary-subtle text-primary',
        CustomRequest::STATUS_COMPLETED => 'bg-success-subtle text-success',
        CustomRequest::STATUS_REJECTED => 'bg-danger-subtle text-danger',
        CustomRequest::STATUS_CANCELLED => 'bg-secondary-subtle text-secondary',
        default => 'bg-light text-muted',
    };

    $nextHint = match ($customRequest->status) {
        CustomRequest::STATUS_REQUESTED => 'Accept to proceed, or reject with a reason.',
        CustomRequest::STATUS_ACCEPTED => 'Start work when ready, or reject if it cannot continue.',
        CustomRequest::STATUS_IN_PROGRESS => 'Mark complete when the customization is finished.',
        default => null,
    };

    $nextStatuses = $customRequest->allowedNextStatuses();
    $canAdvance = $nextStatuses !== [];
    $canReject = in_array(CustomRequest::STATUS_REJECTED, $nextStatuses, true);
    $advanceStatus = collect($nextStatuses)->first(fn ($status) => $status !== CustomRequest::STATUS_REJECTED);

    $advanceLabel = match ($advanceStatus) {
        CustomRequest::STATUS_ACCEPTED => 'Accept request',
        CustomRequest::STATUS_IN_PROGRESS => 'Mark in progress',
        CustomRequest::STATUS_COMPLETED => 'Mark completed',
        default => null,
    };

    $advanceButtonClass = match ($advanceStatus) {
        CustomRequest::STATUS_ACCEPTED, CustomRequest::STATUS_COMPLETED => 'btn-success',
        CustomRequest::STATUS_IN_PROGRESS => 'btn-primary',
        default => 'btn-secondary',
    };
@endphp

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center gap-2">
        <h5 class="card-title mb-0 flex-grow-1">Update status</h5>
        <span class="badge {{ $badgeClass }}">{{ $customRequest->label() }}</span>
    </div>
    <div class="card-body">
        @if (! in_array($customRequest->status, [CustomRequest::STATUS_REJECTED, CustomRequest::STATUS_CANCELLED], true))
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                @php
                    $flowKeys = array_keys($flow);
                    $currentIndex = array_search($customRequest->status, $flowKeys, true);
                @endphp
                @foreach ($flow as $step => $stepLabel)
                    @php
                        $stepIndex = array_search($step, $flowKeys, true);
                        $isDone = $currentIndex !== false && $stepIndex !== false && $stepIndex < $currentIndex;
                        $isCurrent = $customRequest->status === $step;
                        $stepClass = $isCurrent
                            ? 'bg-primary text-white'
                            : ($isDone ? 'bg-success-subtle text-success' : 'bg-light text-muted');
                    @endphp
                    <span class="badge rounded-pill {{ $stepClass }} px-3 py-2">{{ $stepLabel }}</span>
                    @if (! $loop->last)
                        <i class="ri-arrow-right-s-line text-muted"></i>
                    @endif
                @endforeach
            </div>
        @endif

        @if ($nextHint)
            <p class="text-muted mb-3">{{ $nextHint }}</p>
        @endif

        @if (! $canAdvance)
            <p class="text-muted mb-0">No further status changes are available.</p>
        @else
            <div class="row g-3">
                @if ($advanceStatus && $advanceLabel)
                    <div class="col-lg-6">
                        <div class="border rounded p-3 h-100">
                            <div class="fw-medium mb-1">Continue</div>
                            <p class="text-muted small mb-3">Move this request to the next step.</p>
                            <form method="POST" action="{{ $statusUrl }}">
                                @csrf
                                <input type="hidden" name="status" value="{{ $advanceStatus }}">
                                <button type="submit" class="btn {{ $advanceButtonClass }}">{{ $advanceLabel }}</button>
                            </form>
                        </div>
                    </div>
                @endif

                @if ($canReject)
                    <div class="col-lg-6">
                        <div class="border rounded p-3 h-100 border-danger-subtle">
                            <div class="fw-medium mb-1 text-danger">Reject</div>
                            <p class="text-muted small mb-3">Stops the request. A reason is required for the agent.</p>
                            <form method="POST" action="{{ $statusUrl }}">
                                @csrf
                                <input type="hidden" name="status" value="{{ CustomRequest::STATUS_REJECTED }}">
                                <label for="rejection_reason" class="form-label required">Rejection reason</label>
                                <textarea
                                    id="rejection_reason"
                                    name="rejection_reason"
                                    class="form-control mb-3 @error('rejection_reason') is-invalid @enderror"
                                    rows="3"
                                    required
                                    placeholder="Explain why this request cannot proceed"
                                >{{ old('rejection_reason') }}</textarea>
                                @error('rejection_reason')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <button type="submit" class="btn btn-soft-danger">Reject request</button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
