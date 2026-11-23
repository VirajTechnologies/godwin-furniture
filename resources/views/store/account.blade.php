@extends('layouts.store')

@section('title', 'My Account')
@section('body_class', 'gw-theme-body')

@section('content')
    @include('store.partials.header')

    <section class="gw-page-head">
        <div class="gw-page-head-inner">
            <nav aria-label="breadcrumb">
                <ol class="gw-breadcrumb">
                    <li><a href="{{ route('store.home') }}">Home</a></li>
                    <li class="gw-bc-sep">/</li>
                    <li class="is-current" aria-current="page">My Account</li>
                </ol>
            </nav>
            <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
                <div>
                    <h1 class="gw-page-title">My Account</h1>
                    <p class="gw-page-sub">Update contact details, password, and delivery addresses.</p>
                </div>
                <a href="{{ route('store.orders.index') }}" class="gw-view-link">
                    <i class="fas fa-box-open me-1"></i> My Orders
                </a>
            </div>
        </div>
    </section>

    <section class="gw-page-wrap">
        @if (session('success'))
            <div class="gw-alert gw-alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="gw-alert gw-alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row g-4">
            <div class="col-12 col-lg-7">
                <div class="gw-panel mb-4">
                    <h2 class="gw-panel-title">
                        <span><i class="fas fa-user me-2"></i>Contact Details</span>
                    </h2>

                    @if ($errors->hasAny(['name', 'phone', 'email']))
                        <div class="gw-alert gw-alert-danger mb-3">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->get('name') as $error)<li>{{ $error }}</li>@endforeach
                                @foreach ($errors->get('phone') as $error)<li>{{ $error }}</li>@endforeach
                                @foreach ($errors->get('email') as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('store.account.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="gw-label">Full Name</label>
                                <input type="text" id="name" name="name" value="{{ old('name', $customer->name) }}" class="gw-input @error('name') is-invalid @enderror" required maxlength="150" autocomplete="name">
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="gw-label">Phone</label>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone', $customer->phone) }}" class="gw-input @error('phone') is-invalid @enderror" required maxlength="20" autocomplete="tel">
                            </div>
                            <div class="col-12">
                                <label for="email" class="gw-label">Email</label>
                                <input type="email" id="email" name="email" value="{{ old('email', $customer->email ?? $user->email) }}" class="gw-input @error('email') is-invalid @enderror" required maxlength="150" autocomplete="email">
                            </div>
                        </div>
                        <button type="submit" class="gw-btn mt-4">
                            Save Details
                        </button>
                    </form>
                </div>

                <div class="gw-panel">
                    <h2 class="gw-panel-title">
                        <span><i class="fas fa-map-marker-alt me-2"></i>Saved Delivery Addresses</span>
                    </h2>

                    @forelse ($addresses as $address)
                        <div class="d-flex flex-column flex-sm-row justify-content-between gap-3 {{ ! $loop->last ? 'pb-3 mb-3' : 'mb-4' }}" style="{{ ! $loop->last ? 'border-bottom: 1px solid var(--stone);' : '' }}">
                            <div>
                                <div class="fw-medium" style="color: var(--ink);">
                                    {{ $address->displayLabel() }}
                                    @if ($address->is_default)
                                        <span class="gw-badge gw-badge-success ms-1">Default</span>
                                    @endif
                                </div>
                                <div class="small" style="color: var(--mut);">{{ $address->oneLine() }}</div>
                            </div>
                            <div class="d-flex gap-2 align-items-start flex-shrink-0">
                                @unless ($address->is_default)
                                    <form method="POST" action="{{ route('store.account.addresses.default', $address) }}" class="m-0">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="gw-btn gw-btn-sm gw-btn-outline">Set Default</button>
                                    </form>
                                @endunless
                                <form method="POST" action="{{ route('store.account.addresses.destroy', $address) }}" class="m-0" onsubmit="return confirm('Remove this address?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="gw-btn gw-btn-sm gw-btn-danger-outline">Remove</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="small mb-4" style="color: var(--mut);">No saved addresses yet. Add one below or save during checkout.</p>
                    @endforelse

                    <div class="pt-3" style="border-top: 1px solid var(--stone);">
                        <h3 class="gw-panel-subtitle">Add Address</h3>
                        @if ($errors->hasAny(['label', 'address_line', 'city', 'pincode']))
                            <div class="gw-alert gw-alert-danger mb-3">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->get('label') as $error)<li>{{ $error }}</li>@endforeach
                                    @foreach ($errors->get('address_line') as $error)<li>{{ $error }}</li>@endforeach
                                    @foreach ($errors->get('city') as $error)<li>{{ $error }}</li>@endforeach
                                    @foreach ($errors->get('pincode') as $error)<li>{{ $error }}</li>@endforeach
                                </ul>
                            </div>
                        @endif
                        <form method="POST" action="{{ route('store.account.addresses.store') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="label" class="gw-label">Label</label>
                                    <input type="text" id="label" name="label" value="{{ old('label') }}" class="gw-input" maxlength="50" placeholder="Home">
                                </div>
                                <div class="col-md-8">
                                    <label for="address_line" class="gw-label required">Street Address</label>
                                    <input type="text" id="address_line" name="address_line" value="{{ old('address_line') }}" class="gw-input" required maxlength="2000">
                                </div>
                                <div class="col-md-6">
                                    <label for="city" class="gw-label required">City</label>
                                    <input type="text" id="city" name="city" value="{{ old('city') }}" class="gw-input" required maxlength="100">
                                </div>
                                <div class="col-md-6">
                                    <label for="pincode" class="gw-label required">Pincode</label>
                                    <input type="text" id="pincode" name="pincode" value="{{ old('pincode') }}" class="gw-input" required maxlength="10">
                                </div>
                                <div class="col-12">
                                    <div class="form-check mb-3">
                                        <input type="hidden" name="is_default" value="0">
                                        <input class="form-check-input gw-check-input" type="checkbox" id="is_default" name="is_default" value="1" @checked(old('is_default'))>
                                        <label class="form-check-label small" for="is_default">Make default</label>
                                    </div>
                                    <button type="submit" class="gw-btn">Save Address</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-5">
                <div class="gw-panel">
                    <h2 class="gw-panel-title">
                        <span><i class="fas fa-lock me-2"></i>Change Password</span>
                    </h2>

                    @if ($errors->hasAny(['current_password', 'password']))
                        <div class="gw-alert gw-alert-danger mb-3">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->get('current_password') as $error)<li>{{ $error }}</li>@endforeach
                                @foreach ($errors->get('password') as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('store.account.password') }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="current_password" class="gw-label required">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="gw-input @error('current_password') is-invalid @enderror" required autocomplete="current-password">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="gw-label required">New Password</label>
                            <input type="password" id="password" name="password" class="gw-input @error('password') is-invalid @enderror" required autocomplete="new-password">
                        </div>
                        <div class="mb-4">
                            <label for="password_confirmation" class="gw-label required">Confirm New Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="gw-input" required autocomplete="new-password">
                        </div>
                        <button type="submit" class="gw-btn gw-btn-outline">
                            Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    @include('store.partials.footer')
    @include('store.partials.whatsapp')
@endsection
