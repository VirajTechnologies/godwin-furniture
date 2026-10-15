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
        @php
            $destination = old('destination', $transfer->agent_id ? 'agent' : 'branch');
        @endphp
        <div class="col-md-4">
            <label for="destination" class="form-label">Send To</label>
            <select class="form-select @error('destination') is-invalid @enderror" id="destination" name="destination" required>
                <option value="branch" @selected($destination === 'branch')>Branch</option>
                <option value="agent" @selected($destination === 'agent')>Agent</option>
            </select>
            @error('destination')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4" id="branch-field">
            <label for="branch_id" class="form-label">Branch</label>
            <select class="form-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id">
                <option value="">Select Branch</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" data-warehouse="{{ $branch->warehouse?->name }}" @selected((string) old('branch_id', $transfer->branch_id) === (string) $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
            @error('branch_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4" id="agent-field">
            <label for="agent_id" class="form-label">Agent</label>
            <select class="form-select @error('agent_id') is-invalid @enderror" id="agent_id" name="agent_id">
                <option value="">Select Agent</option>
                @foreach ($agents as $agent)
                    <option value="{{ $agent->id }}" data-warehouse="{{ $agent->warehouse?->name }}" @selected((string) old('agent_id', $transfer->agent_id) === (string) $agent->id)>{{ $agent->name }}</option>
                @endforeach
            </select>
            @error('agent_id')
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
            const destinationSelect = document.getElementById('destination');
            const branchField = document.getElementById('branch-field');
            const agentField = document.getElementById('agent-field');
            const branchSelect = document.getElementById('branch_id');
            const agentSelect = document.getElementById('agent_id');
            const warehouseInput = document.getElementById('supplying_warehouse');
            const lines = document.getElementById('transfer-lines');
            const template = document.getElementById('transfer-line-template');
            const addButton = document.getElementById('add-transfer-line');

            function activeSelect() {
                return destinationSelect && destinationSelect.value === 'agent' ? agentSelect : branchSelect;
            }

            function showWarehouse() {
                const select = activeSelect();
                const option = select ? select.options[select.selectedIndex] : null;
                if (warehouseInput) {
                    warehouseInput.value = option ? (option.dataset.warehouse || '') : '';
                }
            }

            function syncDestination() {
                const agent = destinationSelect && destinationSelect.value === 'agent';
                if (branchField) {
                    branchField.hidden = agent;
                }
                if (agentField) {
                    agentField.hidden = !agent;
                }
                if (branchSelect) {
                    branchSelect.required = !agent;
                    branchSelect.disabled = agent;
                }
                if (agentSelect) {
                    agentSelect.required = agent;
                    agentSelect.disabled = !agent;
                }
                showWarehouse();
            }

            if (destinationSelect) {
                destinationSelect.addEventListener('change', syncDestination);
                syncDestination();
            }
            if (branchSelect) {
                branchSelect.addEventListener('change', showWarehouse);
            }
            if (agentSelect) {
                agentSelect.addEventListener('change', showWarehouse);
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
