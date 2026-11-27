@if ($customRequest->allowedNextStatuses() !== [])
    <div class="card">
        <div class="card-header"><h5 class="card-title mb-0">Update status</h5></div>
        <div class="card-body d-flex flex-wrap gap-2 align-items-start">
            @foreach ($customRequest->allowedNextStatuses() as $next)
                <form method="POST" action="{{ $statusUrl }}" class="d-flex flex-wrap gap-2 align-items-start">
                    @csrf
                    <input type="hidden" name="status" value="{{ $next }}">
                    @if ($next === \App\Models\CustomRequest::STATUS_REJECTED)
                        <input type="text" name="rejection_reason" class="form-control form-control-sm" style="min-width: 16rem" placeholder="Rejection reason" required>
                        <button type="submit" class="btn btn-soft-danger">Reject</button>
                    @elseif ($next === \App\Models\CustomRequest::STATUS_ACCEPTED)
                        <button type="submit" class="btn btn-success">Accept</button>
                    @elseif ($next === \App\Models\CustomRequest::STATUS_IN_PROGRESS)
                        <button type="submit" class="btn btn-primary">Mark in progress</button>
                    @elseif ($next === \App\Models\CustomRequest::STATUS_COMPLETED)
                        <button type="submit" class="btn btn-success">Complete</button>
                    @endif
                </form>
            @endforeach
        </div>
    </div>
@endif
