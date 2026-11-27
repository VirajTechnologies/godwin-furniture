<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Source</label>
        <input type="text" class="form-control" value="{{ $customRequest->isMatched() ? 'From catalog' : 'Not in catalog' }}" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label">Product / model</label>
        <input type="text" class="form-control" value="{{ $customRequest->displayName() }}" readonly>
    </div>
    @if ($customRequest->classificationTrail() !== '')
        <div class="col-12">
            <label class="form-label">Classification</label>
            <input type="text" class="form-control" value="{{ $customRequest->classificationTrail() }}" readonly>
        </div>
    @endif
    @if ($customRequest->description)
        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea class="form-control" rows="3" readonly>{{ $customRequest->description }}</textarea>
        </div>
    @endif
    @if ($customRequest->isMatched())
        <div class="col-md-3">
            <label class="form-label">Width (cm)</label>
            <input type="text" class="form-control" value="{{ $customRequest->width_cm ?? '—' }}" readonly>
        </div>
        <div class="col-md-3">
            <label class="form-label">Depth (cm)</label>
            <input type="text" class="form-control" value="{{ $customRequest->depth_cm ?? '—' }}" readonly>
        </div>
        <div class="col-md-3">
            <label class="form-label">Height (cm)</label>
            <input type="text" class="form-control" value="{{ $customRequest->height_cm ?? '—' }}" readonly>
        </div>
        <div class="col-md-3">
            <label class="form-label">Weight (kg)</label>
            <input type="text" class="form-control" value="{{ $customRequest->weight_kg ?? '—' }}" readonly>
        </div>
        @if (! empty($customRequest->specs))
            <div class="col-12">
                <label class="form-label">Specs</label>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <tbody>
                            @foreach ($customRequest->specs as $label => $value)
                                <tr>
                                    <td class="text-muted" style="width: 40%;">{{ $label }}</td>
                                    <td>{{ $value }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
        @if (! empty($customRequest->variants))
            <div class="col-12">
                <label class="form-label">Variants</label>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Colour</th>
                                <th>Finish</th>
                                <th>Upholstery</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($customRequest->variants as $variant)
                                <tr>
                                    <td>{{ $variant['colour'] ?? '—' }}</td>
                                    <td>{{ $variant['finish'] ?? '—' }}</td>
                                    <td>{{ $variant['upholstery'] ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
    <div class="col-md-6">
        <label class="form-label">Customer</label>
        <input type="text" class="form-control" value="{{ collect([$customRequest->customer_name, $customRequest->customer_phone])->filter()->implode(' · ') ?: '—' }}" readonly>
    </div>
    <div class="col-12">
        <label class="form-label">Customization notes</label>
        <textarea class="form-control" rows="4" readonly>{{ $customRequest->notes }}</textarea>
    </div>
    @if ($customRequest->rejection_reason)
        <div class="col-12">
            <label class="form-label">Rejection reason</label>
            <textarea class="form-control" rows="2" readonly>{{ $customRequest->rejection_reason }}</textarea>
        </div>
    @endif
</div>
