@php
    $lines = old('items');
    if (! is_array($lines) || $lines === []) {
        $lines = [['product_id' => '', 'quantity' => '']];
    }
@endphp

<form method="POST" action="{{ route('branch.stock-requests.store') }}">
    @csrf
    <div class="row g-3">
        <div class="col-12">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
            @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="d-flex align-items-center mt-4 mb-2">
        <h6 class="mb-0 flex-grow-1">Lines</h6>
        <button type="button" class="btn btn-soft-primary btn-sm" id="add-request-line">Add Line</button>
    </div>
    @error('items')
        <div class="text-danger small mb-2">{{ $message }}</div>
    @enderror

    <div id="request-lines">
        @foreach ($lines as $index => $line)
            <div class="row g-3 align-items-start request-line mb-3">
                <div class="col-md-7">
                    <label class="form-label">Product</label>
                    <select class="form-select @error('items.'.$index.'.product_id') is-invalid @enderror" name="items[{{ $index }}][product_id]" required>
                        <option value="">Select Product</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected((string) ($line['product_id'] ?? '') === (string) $product->id)>{{ $product->code }} · {{ $product->name }}</option>
                        @endforeach
                    </select>
                    @error('items.'.$index.'.product_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" class="form-control @error('items.'.$index.'.quantity') is-invalid @enderror" name="items[{{ $index }}][quantity]" value="{{ $line['quantity'] ?? '' }}" min="1" step="1" required>
                    @error('items.'.$index.'.quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label d-block">&nbsp;</label>
                    <button type="button" class="btn btn-soft-danger btn-sm remove-request-line">Remove</button>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-2 d-flex gap-2">
        <button type="submit" class="btn btn-success">Send Request</button>
        <a href="{{ route('branch.stock-requests.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>

<template id="request-line-template">
    <div class="row g-3 align-items-start request-line mb-3">
        <div class="col-md-7">
            <label class="form-label">Product</label>
            <select class="form-select" name="items[__INDEX__][product_id]" required>
                <option value="">Select Product</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}">{{ $product->code }} · {{ $product->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Quantity</label>
            <input type="number" class="form-control" name="items[__INDEX__][quantity]" min="1" step="1" required>
        </div>
        <div class="col-md-2">
            <label class="form-label d-block">&nbsp;</label>
            <button type="button" class="btn btn-soft-danger btn-sm remove-request-line">Remove</button>
        </div>
    </div>
</template>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const lines = document.getElementById('request-lines');
            const template = document.getElementById('request-line-template');
            const addButton = document.getElementById('add-request-line');
            if (!lines || !template || !addButton) {
                return;
            }
            addButton.addEventListener('click', function () {
                const index = lines.querySelectorAll('.request-line').length;
                lines.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', String(index)));
            });
            lines.addEventListener('click', function (event) {
                const button = event.target.closest('.remove-request-line');
                if (!button || lines.querySelectorAll('.request-line').length === 1) {
                    return;
                }
                button.closest('.request-line').remove();
            });
        });
    </script>
@endpush
