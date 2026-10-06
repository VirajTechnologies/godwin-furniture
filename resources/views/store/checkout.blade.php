@extends('layouts.store')

@section('title', 'Checkout')
@section('body_class', 'bg-light')

@section('content')
    @include('store.partials.header')

    <section class="bg-white border-bottom py-3">
        <div class="container-fluid px-4 px-lg-5">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                <div>
                    <h2 class="font-heading fw-bold text-dark m-0">Checkout</h2>
                    <p class="text-muted small m-0">Choose a saved delivery address or add a new one. Pay when furniture arrives.</p>
                </div>
                <a href="{{ route('store.cart') }}" class="text-amber font-heading fw-semibold text-decoration-none small">
                    <i class="fas fa-arrow-left me-1"></i> Back to Bag
                </a>
            </div>
        </div>
    </section>

    <section class="container-fluid px-4 px-lg-5 my-4">
        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm rounded-3 font-heading">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-3 font-heading">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $defaultSource = old('address_source', $addresses->isNotEmpty() ? 'saved' : 'new');
            $selectedAddressId = (int) old('address_id', $addresses->firstWhere('is_default', true)?->id ?? $addresses->first()?->id);
        @endphp

        <form method="POST" action="{{ route('store.checkout.store') }}" id="checkout-form">
            @csrf
            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                        <h5 class="font-heading fw-bold text-dark pb-3 border-bottom mb-3">
                            <i class="fas fa-user text-amber me-2"></i> Contact
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="small text-muted font-heading">Full Name</div>
                                <div class="fw-semibold font-heading text-dark">{{ $customer->name }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-muted font-heading">Phone</div>
                                <div class="fw-semibold font-heading text-dark">{{ $customer->phone }}</div>
                            </div>
                            @if ($customer->email)
                                <div class="col-12">
                                    <div class="small text-muted font-heading">Email</div>
                                    <div class="fw-semibold font-heading text-dark">{{ $customer->email }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                        <h5 class="font-heading fw-bold text-dark pb-3 border-bottom mb-3">
                            <i class="fas fa-map-marker-alt text-amber me-2"></i> Delivery Address
                        </h5>

                        @if ($addresses->isNotEmpty())
                            <div class="mb-3">
                                @foreach ($addresses as $address)
                                    <label class="d-flex gap-3 border rounded-3 p-3 mb-2" style="cursor: pointer;">
                                        <input type="radio"
                                               class="form-check-input mt-1 js-delivery-choice"
                                               name="delivery_choice"
                                               value="{{ $address->id }}"
                                               @checked($defaultSource === 'saved' && $selectedAddressId === $address->id)>
                                        <span class="flex-grow-1 font-heading">
                                            <span class="fw-semibold text-dark d-block">
                                                {{ $address->displayLabel() }}
                                                @if ($address->is_default)
                                                    <span class="badge bg-success ms-1">Default</span>
                                                @endif
                                            </span>
                                            <span class="small text-muted">{{ $address->oneLine() }}</span>
                                        </span>
                                    </label>
                                @endforeach

                                <label class="d-flex gap-3 border rounded-3 p-3 mb-0" style="cursor: pointer;">
                                    <input type="radio"
                                           class="form-check-input mt-1 js-delivery-choice"
                                           name="delivery_choice"
                                           value="new"
                                           @checked($defaultSource === 'new')>
                                    <span class="font-heading fw-semibold text-dark">Use a new address</span>
                                </label>
                            </div>
                            <input type="hidden" name="address_source" id="address_source" value="{{ $defaultSource }}">
                            <input type="hidden" name="address_id" id="address_id" value="{{ $defaultSource === 'saved' ? $selectedAddressId : '' }}">
                        @else
                            <input type="hidden" name="address_source" value="new">
                        @endif

                        <div id="new-address-fields" class="row g-3 {{ $defaultSource === 'new' || $addresses->isEmpty() ? '' : 'd-none' }}">
                            <div class="col-md-6">
                                <label for="address_label" class="form-label font-heading small fw-semibold">Label <span class="text-muted fw-normal">(optional)</span></label>
                                <input type="text" id="address_label" name="address_label" value="{{ old('address_label') }}" class="form-control font-heading @error('address_label') is-invalid @enderror" maxlength="50" placeholder="Home, Office…">
                            </div>
                            <div class="col-12">
                                <label for="shipping_address" class="form-label font-heading small fw-semibold required">Street Address</label>
                                <textarea id="shipping_address" name="shipping_address" rows="3" class="form-control font-heading @error('shipping_address') is-invalid @enderror" maxlength="2000" autocomplete="street-address" @required($defaultSource === 'new' || $addresses->isEmpty())>{{ old('shipping_address') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="shipping_city" class="form-label font-heading small fw-semibold required">City</label>
                                <input type="text" id="shipping_city" name="shipping_city" value="{{ old('shipping_city') }}" class="form-control form-control-lg font-heading @error('shipping_city') is-invalid @enderror" maxlength="100" autocomplete="address-level2" @required($defaultSource === 'new' || $addresses->isEmpty())>
                            </div>
                            <div class="col-md-6">
                                <label for="shipping_pincode" class="form-label font-heading small fw-semibold required">Pincode</label>
                                <input type="text" id="shipping_pincode" name="shipping_pincode" value="{{ old('shipping_pincode') }}" class="form-control form-control-lg font-heading @error('shipping_pincode') is-invalid @enderror" maxlength="10" autocomplete="postal-code" @required($defaultSource === 'new' || $addresses->isEmpty())>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="hidden" name="save_address" value="0">
                                    <input class="form-check-input" type="checkbox" id="save_address" name="save_address" value="1" @checked(old('save_address', '1') == '1')>
                                    <label class="form-check-label font-heading small" for="save_address">Save this address for next time</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label for="notes" class="form-label font-heading small fw-semibold">Order Notes <span class="text-muted fw-normal">(optional)</span></label>
                            <textarea id="notes" name="notes" rows="2" class="form-control font-heading @error('notes') is-invalid @enderror" maxlength="2000" placeholder="Delivery timing, floor, landmark…">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <h5 class="font-heading fw-bold text-dark pb-3 border-bottom mb-3">
                            <i class="fas fa-money-bill-wave text-amber me-2"></i> Payment
                        </h5>
                        <div class="border rounded-3 p-3 bg-light d-flex align-items-start gap-3">
                            <div class="form-check m-0 pt-1">
                                <input class="form-check-input" type="radio" checked disabled id="pay_cod">
                            </div>
                            <div>
                                <label for="pay_cod" class="font-heading fw-bold text-dark mb-1">Cash on Delivery</label>
                                <p class="small text-muted mb-0 font-heading">Pay when your furniture is delivered. Online payment comes later.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 80px;">
                        <h5 class="font-heading fw-bold text-dark pb-3 border-bottom mb-3"><i class="fas fa-receipt text-amber me-2"></i> Order Summary</h5>

                        <div class="mb-3">
                            @foreach ($lines as $line)
                                <div class="d-flex justify-content-between gap-2 font-heading small mb-2">
                                    <span class="text-muted">{{ $line->product->name }} × {{ $line->quantity }}</span>
                                    <span class="fw-semibold text-dark text-nowrap">₹{{ number_format($line->line_total, 0) }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-between font-heading small mb-2 text-muted border-top pt-3">
                            <span>Bag Subtotal ({{ $itemCount }} {{ $itemCount === 1 ? 'Item' : 'Items' }})</span>
                            <span class="fw-bold text-dark">₹{{ number_format($subtotal, 2) }}</span>
                        </div>

                        <div class="d-flex justify-content-between font-heading small mb-3 text-muted">
                            <span>Delivery & Installation</span>
                            <span class="badge bg-success text-white">To be confirmed</span>
                        </div>

                        <div class="border-top pt-3 mb-4 d-flex justify-content-between align-items-center">
                            <span class="font-heading fw-bold text-dark fs-6">Grand Total:</span>
                            <span class="font-heading fw-bold fs-4 text-dark">₹{{ number_format($subtotal, 2) }}</span>
                        </div>

                        <button type="submit" class="btn btn-primary-luxury w-100 py-3 font-heading fw-bold fs-6 shadow-sm">
                            <i class="fas fa-check me-2"></i> Place Order
                        </button>
                        <p class="text-center mt-3 small text-muted font-heading mb-0">
                            Signed in as {{ $customer->name }}. Pay cash on delivery.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </section>

    @include('store.partials.footer')
    @include('store.partials.whatsapp')
@endsection

@push('scripts')
<script>
(() => {
    const form = document.getElementById('checkout-form');
    if (!form) return;

    const newFields = document.getElementById('new-address-fields');
    const sourceInput = document.getElementById('address_source');
    const addressIdInput = document.getElementById('address_id');
    const choices = form.querySelectorAll('.js-delivery-choice');

    const sync = () => {
        const selected = form.querySelector('.js-delivery-choice:checked');
        const address = document.getElementById('shipping_address');
        const city = document.getElementById('shipping_city');
        const pincode = document.getElementById('shipping_pincode');
        const needsNew = !choices.length || (selected && selected.value === 'new');

        if (choices.length && selected && sourceInput && addressIdInput) {
            if (selected.value === 'new') {
                sourceInput.value = 'new';
                addressIdInput.value = '';
                newFields?.classList.remove('d-none');
            } else {
                sourceInput.value = 'saved';
                addressIdInput.value = selected.value;
                newFields?.classList.add('d-none');
            }
        }

        [address, city, pincode].forEach((el) => {
            if (el) {
                el.required = needsNew;
            }
        });
    };

    choices.forEach((radio) => radio.addEventListener('change', sync));
    sync();
})();
</script>
@endpush
