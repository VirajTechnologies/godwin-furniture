@extends('layouts.store')

@section('title', 'Checkout')
@section('body_class', 'gw-theme-body')

@section('content')
    @include('store.partials.header')

    <section class="gw-page-head">
        <div class="gw-page-head-inner">
            <nav aria-label="breadcrumb">
                <ol class="gw-breadcrumb">
                    <li><a href="{{ route('store.home') }}">Home</a></li>
                    <li class="gw-bc-sep">/</li>
                    <li><a href="{{ route('store.cart') }}">Shopping Bag</a></li>
                    <li class="gw-bc-sep">/</li>
                    <li class="is-current" aria-current="page">Checkout</li>
                </ol>
            </nav>
            <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
                <div>
                    <h1 class="gw-page-title">Checkout</h1>
                    <p class="gw-page-sub">Choose a saved delivery address or add a new one. Pay when furniture arrives.</p>
                </div>
                <a href="{{ route('store.cart') }}" class="gw-view-link">
                    <i class="fas fa-arrow-left me-1"></i> Back to Bag
                </a>
            </div>
        </div>
    </section>

    <section class="gw-page-wrap">
        @if (session('error'))
            <div class="gw-alert gw-alert-danger">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="gw-alert gw-alert-danger">
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
                    <div class="gw-panel mb-4">
                        <h2 class="gw-panel-title">
                            <span><i class="fas fa-user me-2"></i>Contact</span>
                        </h2>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="gw-label mb-1" style="color: var(--mut);">Full Name</div>
                                <div class="fw-medium" style="color: var(--ink);">{{ $customer->name }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="gw-label mb-1" style="color: var(--mut);">Phone</div>
                                <div class="fw-medium" style="color: var(--ink);">{{ $customer->phone }}</div>
                            </div>
                            @if ($customer->email)
                                <div class="col-12">
                                    <div class="gw-label mb-1" style="color: var(--mut);">Email</div>
                                    <div class="fw-medium" style="color: var(--ink);">{{ $customer->email }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="gw-panel mb-4">
                        <h2 class="gw-panel-title">
                            <span><i class="fas fa-map-marker-alt me-2"></i>Delivery Address</span>
                        </h2>

                        @if ($addresses->isNotEmpty())
                            <div class="mb-3">
                                @foreach ($addresses as $address)
                                    <label class="gw-choice-card">
                                        <input type="radio"
                                               class="form-check-input gw-radio-input mt-1 js-delivery-choice"
                                               name="delivery_choice"
                                               value="{{ $address->id }}"
                                               @checked($defaultSource === 'saved' && $selectedAddressId === $address->id)>
                                        <span class="flex-grow-1">
                                            <span class="fw-medium d-block" style="color: var(--ink);">
                                                {{ $address->displayLabel() }}
                                                @if ($address->is_default)
                                                    <span class="gw-badge gw-badge-success ms-1">Default</span>
                                                @endif
                                            </span>
                                            <span class="small" style="color: var(--mut);">{{ $address->oneLine() }}</span>
                                        </span>
                                    </label>
                                @endforeach

                                <label class="gw-choice-card mb-0">
                                    <input type="radio"
                                           class="form-check-input gw-radio-input mt-1 js-delivery-choice"
                                           name="delivery_choice"
                                           value="new"
                                           @checked($defaultSource === 'new')>
                                    <span class="fw-medium" style="color: var(--ink);">Use a new address</span>
                                </label>
                            </div>
                            <input type="hidden" name="address_source" id="address_source" value="{{ $defaultSource }}">
                            <input type="hidden" name="address_id" id="address_id" value="{{ $defaultSource === 'saved' ? $selectedAddressId : '' }}">
                        @else
                            <input type="hidden" name="address_source" value="new">
                        @endif

                        <div id="new-address-fields" class="row g-3 mt-1 {{ $defaultSource === 'new' || $addresses->isEmpty() ? '' : 'd-none' }}">
                            <div class="col-md-6">
                                <label for="address_label" class="gw-label">Label <span class="text-lowercase fw-normal" style="color: var(--mut);">(optional)</span></label>
                                <input type="text" id="address_label" name="address_label" value="{{ old('address_label') }}" class="gw-input @error('address_label') is-invalid @enderror" maxlength="50" placeholder="Home, Office…">
                            </div>
                            <div class="col-12">
                                <label for="shipping_address" class="gw-label required">Street Address</label>
                                <textarea id="shipping_address" name="shipping_address" rows="3" class="gw-textarea @error('shipping_address') is-invalid @enderror" maxlength="2000" autocomplete="street-address" @required($defaultSource === 'new' || $addresses->isEmpty())>{{ old('shipping_address') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="shipping_city" class="gw-label required">City</label>
                                <input type="text" id="shipping_city" name="shipping_city" value="{{ old('shipping_city') }}" class="gw-input @error('shipping_city') is-invalid @enderror" maxlength="100" autocomplete="address-level2" @required($defaultSource === 'new' || $addresses->isEmpty())>
                            </div>
                            <div class="col-md-6">
                                <label for="shipping_pincode" class="gw-label required">Pincode</label>
                                <input type="text" id="shipping_pincode" name="shipping_pincode" value="{{ old('shipping_pincode') }}" class="gw-input @error('shipping_pincode') is-invalid @enderror" maxlength="10" autocomplete="postal-code" @required($defaultSource === 'new' || $addresses->isEmpty())>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="hidden" name="save_address" value="0">
                                    <input class="form-check-input gw-check-input" type="checkbox" id="save_address" name="save_address" value="1" @checked(old('save_address', '1') == '1')>
                                    <label class="form-check-label small" for="save_address">Save this address for next time</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label for="notes" class="gw-label">Order Notes <span class="text-lowercase fw-normal" style="color: var(--mut);">(optional)</span></label>
                            <textarea id="notes" name="notes" rows="2" class="gw-textarea @error('notes') is-invalid @enderror" maxlength="2000" placeholder="Delivery timing, floor, landmark…">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="gw-panel">
                        <h2 class="gw-panel-title">
                            <span><i class="fas fa-money-bill-wave me-2"></i>Payment</span>
                        </h2>
                        <div class="gw-choice-card mb-0" style="background: var(--bg); cursor: default;">
                            <div class="form-check m-0 pt-1">
                                <input class="form-check-input gw-radio-input" type="radio" checked disabled id="pay_cod">
                            </div>
                            <div>
                                <label for="pay_cod" class="fw-semibold d-block mb-1" style="color: var(--ink);">Cash on Delivery</label>
                                <p class="small mb-0" style="color: var(--mut);">Pay when your furniture is delivered. Online payment comes later.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="gw-panel sticky-top" style="top: 86px;">
                        <h2 class="gw-panel-title">
                            <span><i class="fas fa-receipt me-2"></i>Order Summary</span>
                        </h2>

                        <div class="mb-3">
                            @foreach ($lines as $line)
                                <div class="d-flex justify-content-between gap-2 small mb-2">
                                    <span style="color: var(--mut);">{{ $line->variant->displayName() }} × {{ $line->quantity }}</span>
                                    <span class="fw-semibold text-nowrap" style="color: var(--ink);">₹{{ number_format($line->line_total, 0) }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-between small mb-2 pt-3" style="border-top: 1px solid var(--stone); color: var(--mut);">
                            <span>Bag Subtotal ({{ $itemCount }} {{ $itemCount === 1 ? 'Item' : 'Items' }})</span>
                            <span class="fw-semibold" style="color: var(--ink);">₹{{ number_format($subtotal, 2) }}</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center small mb-3" style="color: var(--mut);">
                            <span>Delivery &amp; Installation</span>
                            <span class="gw-badge gw-badge-success">To be confirmed</span>
                        </div>

                        <div class="pt-3 mb-4 d-flex justify-content-between align-items-center" style="border-top: 1px solid var(--stone);">
                            <span class="fw-semibold" style="color: var(--ink);">Grand Total</span>
                            <span style="font-family: 'Cormorant Garamond', Georgia, serif; font-size: 1.75rem; font-weight: 600; color: var(--ink);">₹{{ number_format($subtotal, 2) }}</span>
                        </div>

                        <button type="submit" class="gw-btn w-100 text-center py-3">
                            <i class="fas fa-check me-2"></i> Place Order
                        </button>
                        <p class="text-center mt-3 small mb-0" style="color: var(--mut);">
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
