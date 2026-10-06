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
            <label for="phone" class="form-label">Phone</label>
            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" required data-lookup-url="{{ route('branch.customers.lookup') }}">
            <div class="form-text" id="phone-lookup-note">Enter the phone. After 10 digits, the name and email fill in if this customer has bought before. Existing details are not changed here.</div>
            <div class="text-danger small mt-1 d-none" id="phone-inactive">This customer is inactive.</div>
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="customer_name" class="form-label">Customer Name</label>
            <input type="text" class="form-control @error('customer_name') is-invalid @enderror" id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required>
            @error('customer_name')
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
        <div class="col-md-8">
            <label class="form-label d-block required">Fulfilment</label>
            @php $deliveryType = old('delivery_type', \App\Models\Order::DELIVERY_PICKUP); @endphp
            <div class="d-flex flex-wrap gap-3">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="delivery_type" id="delivery_type_pickup" value="{{ \App\Models\Order::DELIVERY_PICKUP }}" @checked($deliveryType === \App\Models\Order::DELIVERY_PICKUP)>
                    <label class="form-check-label" for="delivery_type_pickup">Collect at store</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="delivery_type" id="delivery_type_delivery" value="{{ \App\Models\Order::DELIVERY_DELIVERY }}" @checked($deliveryType === \App\Models\Order::DELIVERY_DELIVERY)>
                    <label class="form-check-label" for="delivery_type_delivery">Door delivery</label>
                </div>
            </div>
            @error('delivery_type')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12 {{ $deliveryType === \App\Models\Order::DELIVERY_DELIVERY ? '' : 'd-none' }}" id="delivery-address-wrap">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="shipping_address" class="form-label">Delivery Address</label>
                    <textarea class="form-control @error('shipping_address') is-invalid @enderror" id="shipping_address" name="shipping_address" rows="2">{{ old('shipping_address') }}</textarea>
                    <div class="form-text" id="delivery-address-note">Required for door delivery. Fills from the customer’s saved address when available.</div>
                    @error('shipping_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="shipping_city" class="form-label">City</label>
                    <input type="text" class="form-control @error('shipping_city') is-invalid @enderror" id="shipping_city" name="shipping_city" value="{{ old('shipping_city') }}">
                    @error('shipping_city')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="shipping_pincode" class="form-label">Pincode</label>
                    <input type="text" class="form-control @error('shipping_pincode') is-invalid @enderror" id="shipping_pincode" name="shipping_pincode" value="{{ old('shipping_pincode') }}">
                    @error('shipping_pincode')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
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
                            <option value="{{ $stock->product_id }}" data-price="{{ $stock->product->priceForBranch(auth()->user()->employee->branch_id) }}" data-available="{{ $stock->quantity }}" @selected((string) ($line['product_id'] ?? '') === (string) $stock->product_id)>{{ $stock->product->code }} · {{ $stock->product->name }}</option>
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
                    <option value="{{ $stock->product_id }}" data-price="{{ $stock->product->priceForBranch(auth()->user()->employee->branch_id) }}" data-available="{{ $stock->quantity }}">{{ $stock->product->code }} · {{ $stock->product->name }}</option>
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
            const phone = document.getElementById('phone');
            const customerName = document.getElementById('customer_name');
            const email = document.getElementById('email');
            const lookupNote = document.getElementById('phone-lookup-note');
            const inactiveNote = document.getElementById('phone-inactive');
            const deliveryWrap = document.getElementById('delivery-address-wrap');
            const shippingAddress = document.getElementById('shipping_address');
            const shippingCity = document.getElementById('shipping_city');
            const shippingPincode = document.getElementById('shipping_pincode');
            const deliveryAddressNote = document.getElementById('delivery-address-note');
            let lastPhone = '';
            let filledFromLookup = false;
            let addressFromLookup = false;

            function syncDeliveryFields() {
                const isDelivery = document.getElementById('delivery_type_delivery').checked;
                deliveryWrap.classList.toggle('d-none', !isDelivery);
                shippingAddress.required = isDelivery;
                shippingCity.required = isDelivery;
                shippingPincode.required = isDelivery;
            }

            function applyLookupAddress(address) {
                if (! address) {
                    return;
                }
                shippingAddress.value = address.address_line || '';
                shippingCity.value = address.city || '';
                shippingPincode.value = address.pincode || '';
                addressFromLookup = true;
                deliveryAddressNote.textContent = 'Filled from the customer’s saved address. Change it if this delivery goes elsewhere.';
            }

            function clearLookupAddress() {
                if (! addressFromLookup) {
                    return;
                }
                shippingAddress.value = '';
                shippingCity.value = '';
                shippingPincode.value = '';
                addressFromLookup = false;
                deliveryAddressNote.textContent = 'Required for door delivery. Fills from the customer’s saved address when available.';
            }

            function setCustomerFieldsLocked(locked) {
                customerName.readOnly = locked;
                email.readOnly = locked;
            }

            function clearFilledCustomer() {
                if (!filledFromLookup) {
                    return;
                }
                customerName.value = '';
                email.value = '';
                filledFromLookup = false;
                setCustomerFieldsLocked(false);
                clearLookupAddress();
            }

            function lookupCustomer() {
                const value = phone.value.trim();
                inactiveNote.classList.add('d-none');

                if (value === '') {
                    clearFilledCustomer();
                    lastPhone = '';
                    lookupNote.textContent = "Enter the phone. After 10 digits, the name and email fill in if this customer has bought before. Existing details are not changed here.";
                    return;
                }

                if (value === lastPhone) {
                    return;
                }

                const previousFill = filledFromLookup;
                lastPhone = value;

                fetch(phone.dataset.lookupUrl + '?phone=' + encodeURIComponent(value), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(function (response) {
                    if (!response.ok) {
                        return null;
                    }
                    return response.json();
                }).then(function (data) {
                    if (!data || phone.value.trim() !== value) {
                        return;
                    }
                    if (data.found) {
                        customerName.value = data.name || '';
                        email.value = data.email || '';
                        filledFromLookup = true;
                        setCustomerFieldsLocked(true);
                        lookupNote.textContent = 'Customer found. Name and email are read-only — customers update those in their online account.';
                        inactiveNote.classList.toggle('d-none', data.active !== false);
                        if (data.address) {
                            applyLookupAddress(data.address);
                        } else {
                            clearLookupAddress();
                        }
                        return;
                    }
                    if (previousFill) {
                        customerName.value = '';
                        email.value = '';
                    }
                    filledFromLookup = false;
                    setCustomerFieldsLocked(false);
                    clearLookupAddress();
                    lookupNote.textContent = 'No customer has this phone. Enter name (and email if known); a new customer will be saved with this sale.';
                });
            }

            document.querySelectorAll('input[name="delivery_type"]').forEach(function (input) {
                input.addEventListener('change', syncDeliveryFields);
            });
            syncDeliveryFields();

            phone.addEventListener('input', function () {
                if (/^\d{10}$/.test(phone.value.trim())) {
                    lookupCustomer();
                }
            });
            phone.addEventListener('blur', lookupCustomer);

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
