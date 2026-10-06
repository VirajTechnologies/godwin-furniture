@php
    $isEdit = $transfer->exists;
    $lines = old('items');
    if (! is_array($lines)) {
        $lines = $isEdit
            ? $transfer->items->map(fn ($item) => ['product_id' => $item->product_id, 'quantity' => $item->quantity])->all()
            : [];
    }
    if ($lines === []) {
        $lines = [['product_id' => '', 'quantity' => '']];
    }
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.transfers.update', $transfer) : route('admin.transfers.store') }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-md-4">
            <label for="code" class="form-label">Code</label>
            @if ($isEdit)
                <input type="text" class="form-control" id="code" value="{{ $transfer->code }}" readonly>
            @else
                <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" placeholder="TR001" required>
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            @endif
        </div>
        <div class="col-md-4">
            <label for="branch_id" class="form-label">Branch</label>
            <select class="form-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id" required>
                <option value="">Select Branch</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" data-warehouse="{{ $branch->warehouse?->name }}" @selected((string) old('branch_id', $transfer->branch_id) === (string) $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
            @error('branch_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="supplying_warehouse" class="form-label">Supplying Warehouse</label>
            <input type="text" class="form-control" id="supplying_warehouse" value="{{ $transfer->warehouse?->name }}" readonly>
        </div>
        <div class="col-12">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="2">{{ old('notes', $transfer->notes) }}</textarea>
            @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="d-flex align-items-center mt-4 mb-2">
        <h6 class="mb-0 flex-grow-1">Lines</h6>
        <button type="button" class="btn btn-soft-primary btn-sm" id="add-transfer-line">Add Line</button>
    </div>
    @error('items')
        <div class="text-danger small mb-2">{{ $message }}</div>
    @enderror

    <div id="transfer-lines">
        @foreach ($lines as $index => $line)
            <div class="row g-3 align-items-start transfer-line mb-3">
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
                    <button type="button" class="btn btn-soft-danger btn-sm remove-transfer-line">Remove</button>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-2 d-flex gap-2">
        <button type="submit" class="btn btn-success">Save Transfer</button>
        @if ($isEdit && $transfer->isDraft())
            <button
                type="submit"
                class="btn btn-warning"
                formaction="{{ route('admin.transfers.dispatch', $transfer) }}"
                onclick="return confirm('Save and dispatch this transfer? Warehouse stock will decrease.')"
            >Save &amp; Dispatch</button>
        @endif
        <a href="{{ route('admin.transfers.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>

<template id="transfer-line-template">
    <div class="row g-3 align-items-start transfer-line mb-3">
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
            <button type="button" class="btn btn-soft-danger btn-sm remove-transfer-line">Remove</button>
        </div>
    </div>
</template>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const branchSelect = document.getElementById('branch_id');
            const warehouseInput = document.getElementById('supplying_warehouse');
            const lines = document.getElementById('transfer-lines');
            const template = document.getElementById('transfer-line-template');
            const addButton = document.getElementById('add-transfer-line');

            function showWarehouse() {
                const option = branchSelect.options[branchSelect.selectedIndex];
                warehouseInput.value = option ? (option.dataset.warehouse || '') : '';
            }

            if (branchSelect && warehouseInput) {
                branchSelect.addEventListener('change', showWarehouse);
                if (branchSelect.value) {
                    showWarehouse();
                }
            }

            if (!lines || !template || !addButton) {
                return;
            }

            addButton.addEventListener('click', function () {
                const index = lines.querySelectorAll('.transfer-line').length;
                const html = template.innerHTML.replaceAll('__INDEX__', String(index));
                lines.insertAdjacentHTML('beforeend', html);
            });

            lines.addEventListener('click', function (event) {
                const button = event.target.closest('.remove-transfer-line');
                if (!button) {
                    return;
                }
                const rows = lines.querySelectorAll('.transfer-line');
                if (rows.length === 1) {
                    return;
                }
                button.closest('.transfer-line').remove();
            });
        });
    </script>
@endpush
