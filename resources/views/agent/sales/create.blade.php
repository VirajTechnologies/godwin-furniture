@extends('layouts.agent')

@section('title', 'New Sale')
@section('page_title', 'New Sale')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('agent.sales.index') }}">Sales</a></li>
    <li class="breadcrumb-item active">New</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header"><h5 class="card-title mb-0">Sale Details</h5></div>
        <div class="card-body">
            @if ($terms->isEmpty())
                <p class="text-muted mb-0">There is nothing to sell yet. A product appears here after head office sets its minimum price and commission, and this shop receives stock.</p>
            @else
                @php
                    $lines = old('items');
                    if (! is_array($lines) || $lines === []) {
                        $lines = [['product_id' => '', 'quantity' => 1, 'unit_price' => '']];
                    }
                    $deliveryType = old('delivery_type', \App\Models\Order::DELIVERY_PICKUP);
                @endphp
                <form method="POST" action="{{ route('agent.sales.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="phone" class="form-label required">Phone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" required data-lookup-url="{{ route('agent.customers.lookup') }}">
                            <div class="form-text" id="phone-lookup-note">Enter the phone. After 10 digits, the name and email fill in if this customer has bought before. Existing details are not changed here.</div>
                            <div class="text-danger small mt-1 d-none" id="phone-inactive">This customer is inactive.</div>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="customer_name" class="form-label required">Customer Name</label>
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
                            <label for="method" class="form-label required">Payment</label>
                            <select class="form-select @error('method') is-invalid @enderror" id="method" name="method" required>
                                <option value="cash" @selected(old('method', 'cash') === 'cash')>Cash</option>
                                <option value="upi" @selected(old('method') === 'upi')>UPI</option>
                                <option value="card" @selected(old('method') === 'card')>Card</option>
                            </select>
                            @error('method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label d-block required">Fulfilment</label>
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
                    @error('items')
                        <div class="text-danger small mb-2">{{ $message }}</div>
                    @enderror

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>On Hand</th>
                                    <th>Minimum</th>
                                    <th>MRP</th>
                                    <th>Commission</th>
                                    <th>Quantity</th>
                                    <th>Selling Price</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="sale-lines">
                                @foreach ($lines as $index => $line)
                                    @php
                                        $selected = $terms->firstWhere('product_id', (int) ($line['product_id'] ?? 0));
                                    @endphp
                                    <tr class="sale-line">
                                        <td style="min-width: 240px;">
                                            <select class="form-select agent-product @error('items.'.$index.'.product_id') is-invalid @enderror" name="items[{{ $index }}][product_id]" required>
                                                <option value="">Select Product</option>
                                                @foreach ($terms as $term)
                                                    <option
                                                        value="{{ $term->product_id }}"
                                                        data-stock="{{ $stocks[$term->product_id] ?? 0 }}"
                                                        data-min="{{ $term->minimum_price }}"
                                                        data-mrp="{{ $term->product?->mrp() }}"
                                                        data-commission="{{ $term->commission_percent }}"
                                                        @selected((string) ($line['product_id'] ?? '') === (string) $term->product_id)
                                                    >{{ $term->product?->code }} · {{ $term->product?->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('items.'.$index.'.product_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td class="agent-stock">{{ $selected ? ($stocks[$selected->product_id] ?? '—') : '—' }}</td>
                                        <td class="agent-min">{{ $selected ? '₹'.number_format((float) $selected->minimum_price, 2) : '—' }}</td>
                                        <td class="agent-mrp">{{ $selected?->product?->mrp() !== null ? '₹'.number_format($selected->product->mrp(), 2) : '—' }}</td>
                                        <td class="agent-commission">{{ $selected ? rtrim(rtrim(number_format((float) $selected->commission_percent, 2), '0'), '.').'%' : '—' }}</td>
                                        <td style="max-width: 120px;">
                                            <input type="number" class="form-control sale-quantity @error('items.'.$index.'.quantity') is-invalid @enderror" name="items[{{ $index }}][quantity]" value="{{ $line['quantity'] ?? 1 }}" min="1" step="1" required>
                                            @error('items.'.$index.'.quantity')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td style="max-width: 160px;">
                                            <input type="number" class="form-control sale-price @error('items.'.$index.'.unit_price') is-invalid @enderror" name="items[{{ $index }}][unit_price]" value="{{ $line['unit_price'] ?? '' }}" min="0" step="0.01" required>
                                            @error('items.'.$index.'.unit_price')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-soft-danger btn-sm remove-sale-line">Remove</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end">
                        <h5 class="mb-0">Total <span id="sale-total">₹0.00</span></h5>
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-success">Save Sale</button>
                        <a href="{{ route('agent.sales.index') }}" class="btn btn-light">Cancel</a>
                    </div>
                </form>

                <template id="sale-line-template">
                    <tr class="sale-line">
                        <td style="min-width: 240px;">
                            <select class="form-select agent-product" name="items[__INDEX__][product_id]" required>
                                <option value="">Select Product</option>
                                @foreach ($terms as $term)
                                    <option
                                        value="{{ $term->product_id }}"
                                        data-stock="{{ $stocks[$term->product_id] ?? 0 }}"
                                        data-min="{{ $term->minimum_price }}"
                                        data-mrp="{{ $term->product?->mrp() }}"
                                        data-commission="{{ $term->commission_percent }}"
                                    >{{ $term->product?->code }} · {{ $term->product?->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="agent-stock">—</td>
                        <td class="agent-min">—</td>
                        <td class="agent-mrp">—</td>
                        <td class="agent-commission">—</td>
                        <td style="max-width: 120px;">
                            <input type="number" class="form-control sale-quantity" name="items[__INDEX__][quantity]" value="1" min="1" step="1" required>
                        </td>
                        <td style="max-width: 160px;">
                            <input type="number" class="form-control sale-price" name="items[__INDEX__][unit_price]" min="0" step="0.01" required>
                        </td>
                        <td>
                            <button type="button" class="btn btn-soft-danger btn-sm remove-sale-line">Remove</button>
                        </td>
                    </tr>
                </template>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const lines = document.getElementById('sale-lines');
            const template = document.getElementById('sale-line-template');
            const addButton = document.getElementById('add-sale-line');
            const total = document.getElementById('sale-total');
            const phone = document.getElementById('phone');
            if (!lines || !phone) {
                return;
            }

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

            function money(value) {
                const amount = Number(value);
                return Number.isFinite(amount) && value !== '' ? '₹' + amount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—';
            }

            function showRow(select) {
                const option = select.options[select.selectedIndex];
                const row = select.closest('tr');
                row.querySelector('.agent-stock').textContent = option && option.dataset.stock ? option.dataset.stock : '—';
                row.querySelector('.agent-min').textContent = option && option.dataset.min ? money(option.dataset.min) : '—';
                row.querySelector('.agent-mrp').textContent = option && option.dataset.mrp ? money(option.dataset.mrp) : '—';
                row.querySelector('.agent-commission').textContent = option && option.dataset.commission ? option.dataset.commission.replace(/\.00$/, '') + '%' : '—';
            }

            function refreshTotal() {
                let sum = 0;
                lines.querySelectorAll('.sale-line').forEach(function (row) {
                    const price = Number(row.querySelector('.sale-price').value || 0);
                    const count = Number(row.querySelector('.sale-quantity').value || 0);
                    if (Number.isFinite(price) && Number.isFinite(count)) {
                        sum += price * count;
                    }
                });
                total.textContent = '₹' + sum.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function syncDeliveryFields() {
                const isDelivery = document.getElementById('delivery_type_delivery').checked;
                deliveryWrap.classList.toggle('d-none', !isDelivery);
                shippingAddress.required = isDelivery;
                shippingCity.required = isDelivery;
                shippingPincode.required = isDelivery;
            }

            function applyLookupAddress(address) {
                if (!address) {
                    return;
                }
                shippingAddress.value = address.address_line || '';
                shippingCity.value = address.city || '';
                shippingPincode.value = address.pincode || '';
                addressFromLookup = true;
                deliveryAddressNote.textContent = 'Filled from the customer’s saved address. Change it if this delivery goes elsewhere.';
            }

            function clearLookupAddress() {
                if (!addressFromLookup) {
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
                    lookupNote.textContent = 'Enter the phone. After 10 digits, the name and email fill in if this customer has bought before. Existing details are not changed here.';
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

            addButton.addEventListener('click', function () {
                const index = lines.querySelectorAll('.sale-line').length;
                lines.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', String(index)));
                refreshTotal();
            });

            lines.addEventListener('click', function (event) {
                const button = event.target.closest('.remove-sale-line');
                if (!button || lines.querySelectorAll('.sale-line').length === 1) {
                    return;
                }
                button.closest('.sale-line').remove();
                refreshTotal();
            });

            lines.addEventListener('change', function (event) {
                if (event.target.classList.contains('agent-product')) {
                    showRow(event.target);
                }
                refreshTotal();
            });
            lines.addEventListener('input', refreshTotal);
            lines.querySelectorAll('.agent-product').forEach(showRow);
            refreshTotal();
        });
    </script>
@endpush
