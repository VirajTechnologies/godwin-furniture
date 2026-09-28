@php
    $lines = old('items');
    if (! is_array($lines) || $lines === []) {
        $lines = [['product_id' => '', 'quantity' => '']];
    }
@endphp

<form method="POST" action="{{ route('branch.sales.store') }}">
    @csrf
    <div class="row g-3">
        <div class="col-md-4">
            <label for="customer_name" class="form-label">Customer Name</label>
            <input type="text" class="form-control @error('customer_name') is-invalid @enderror" id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required>
            @error('customer_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" required>
            <div class="form-text">The same phone is used again for a returning customer.</div>
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="payment_method" class="form-label">Payment Method</label>
            <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method" required>
                <option value="cash" @selected(old('payment_method', 'cash') === 'cash')>Cash</option>
                <option value="upi" @selected(old('payment_method') === 'upi')>UPI</option>
                <option value="card" @selected(old('payment_method') === 'card')>Card</option>
            </select>
            @error('payment_method')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
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
        <button type="button" class="btn btn-soft-primary btn-sm" id="add-sale-line">Add Line</button>
    </div>

    <div id="sale-lines">
        @foreach ($lines as $index => $line)
            <div class="row g-3 align-items-start sale-line mb-3">
                <div class="col-md-6">
                    <label class="form-label">Product</label>
                    <select class="form-select sale-product @error('items.'.$index.'.product_id') is-invalid @enderror" name="items[{{ $index }}][product_id]" required>
                        <option value="">Select Product</option>
                        @foreach ($stocks as $stock)
                            <option value="{{ $stock->product_id }}" data-price="{{ $stock->product->selling_price }}" data-available="{{ $stock->quantity }}" @selected((string) ($line['product_id'] ?? '') === (string) $stock->product_id)>{{ $stock->product->code }} · {{ $stock->product->name }}</option>
                        @endforeach
                    </select>
                    @error('items.'.$index.'.product_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">Quantity</label>
                    <input type="number" class="form-control sale-quantity @error('items.'.$index.'.quantity') is-invalid @enderror" name="items[{{ $index }}][quantity]" value="{{ $line['quantity'] ?? '' }}" min="1" step="1" required>
                    @error('items.'.$index.'.quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">Price</label>
                    <input type="text" class="form-control sale-price" value="" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label d-block">&nbsp;</label>
                    <button type="button" class="btn btn-soft-danger btn-sm remove-sale-line">Remove</button>
                </div>
            </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-end">
        <h5 class="mb-0">Total <span id="sale-total">₹0.00</span></h5>
    </div>

    <div class="mt-3 d-flex gap-2">
        <button type="submit" class="btn btn-success">Save Sale</button>
        <a href="{{ route('branch.sales.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>

<template id="sale-line-template">
    <div class="row g-3 align-items-start sale-line mb-3">
        <div class="col-md-6">
            <label class="form-label">Product</label>
            <select class="form-select sale-product" name="items[__INDEX__][product_id]" required>
                <option value="">Select Product</option>
                @foreach ($stocks as $stock)
                    <option value="{{ $stock->product_id }}" data-price="{{ $stock->product->selling_price }}" data-available="{{ $stock->quantity }}">{{ $stock->product->code }} · {{ $stock->product->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Quantity</label>
            <input type="number" class="form-control sale-quantity" name="items[__INDEX__][quantity]" min="1" step="1" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Price</label>
            <input type="text" class="form-control sale-price" value="" readonly>
        </div>
        <div class="col-md-2">
            <label class="form-label d-block">&nbsp;</label>
            <button type="button" class="btn btn-soft-danger btn-sm remove-sale-line">Remove</button>
        </div>
    </div>
</template>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const lines = document.getElementById('sale-lines');
            const template = document.getElementById('sale-line-template');
            const addButton = document.getElementById('add-sale-line');
            const total = document.getElementById('sale-total');

            function money(amount) {
                return '₹' + amount.toFixed(2);
            }

            function refresh() {
                let sum = 0;
                lines.querySelectorAll('.sale-line').forEach(function (row) {
                    const select = row.querySelector('.sale-product');
                    const quantity = row.querySelector('.sale-quantity');
                    const price = row.querySelector('.sale-price');
                    const option = select.options[select.selectedIndex];
                    const unit = option && option.dataset.price ? Number(option.dataset.price) : 0;
                    const count = Number(quantity.value || 0);
                    price.value = option && option.value ? money(unit) : '';
                    sum += unit * count;
                });
                total.textContent = money(sum);
            }

            addButton.addEventListener('click', function () {
                const index = lines.querySelectorAll('.sale-line').length;
                lines.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', String(index)));
            });

            lines.addEventListener('click', function (event) {
                const button = event.target.closest('.remove-sale-line');
                if (!button || lines.querySelectorAll('.sale-line').length === 1) {
                    return;
                }
                button.closest('.sale-line').remove();
                refresh();
            });

            lines.addEventListener('input', refresh);
            lines.addEventListener('change', refresh);
            refresh();
        });
    </script>
@endpush
