@extends('layouts.store')

@section('title', 'My Account')
@section('body_class', 'bg-light')

@section('content')
    @include('store.partials.header')

    <section class="bg-white border-bottom py-3">
        <div class="container-fluid px-4 px-lg-5">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                <div>
                    <h2 class="font-heading fw-bold text-dark m-0">My Account</h2>
                    <p class="text-muted small m-0">Update contact details, password, and delivery addresses.</p>
                </div>
                <a href="{{ route('store.orders.index') }}" class="text-amber font-heading fw-semibold text-decoration-none small">
                    <i class="fas fa-box-open me-1"></i> My Orders
                </a>
            </div>
        </div>
    </section>

    <section class="container-fluid px-4 px-lg-5 my-4">
        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-3 font-heading">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm rounded-3 font-heading">{{ session('error') }}</div>
        @endif

        <div class="row g-4">
            <div class="col-12 col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="font-heading fw-bold text-dark pb-3 border-bottom mb-3">
                        <i class="fas fa-user text-amber me-2"></i> Contact Details
                    </h5>

                    @if ($errors->hasAny(['name', 'phone', 'email']))
                        <div class="alert alert-danger border-0 rounded-3 small font-heading mb-3">
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
                                <label for="name" class="form-label font-heading small fw-semibold">Full Name</label>
                                <input type="text" id="name" name="name" value="{{ old('name', $customer->name) }}" class="form-control form-control-lg font-heading @error('name') is-invalid @enderror" required maxlength="150" autocomplete="name">
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label font-heading small fw-semibold">Phone</label>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone', $customer->phone) }}" class="form-control form-control-lg font-heading @error('phone') is-invalid @enderror" required maxlength="20" autocomplete="tel">
                            </div>
                            <div class="col-12">
                                <label for="email" class="form-label font-heading small fw-semibold">Email</label>
                                <input type="email" id="email" name="email" value="{{ old('email', $customer->email ?? $user->email) }}" class="form-control form-control-lg font-heading @error('email') is-invalid @enderror" required maxlength="150" autocomplete="email">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary-luxury font-heading fw-bold mt-4 px-4">
                            Save Details
                        </button>
                    </form>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h5 class="font-heading fw-bold text-dark pb-3 border-bottom mb-3">
                        <i class="fas fa-map-marker-alt text-amber me-2"></i> Saved Delivery Addresses
                    </h5>

                    @forelse ($addresses as $address)
                        <div class="d-flex flex-column flex-sm-row justify-content-between gap-3 {{ ! $loop->last ? 'pb-3 border-bottom mb-3' : 'mb-4' }}">
                            <div class="font-heading">
                                <div class="fw-semibold text-dark">
                                    {{ $address->displayLabel() }}
                                    @if ($address->is_default)
                                        <span class="badge bg-success ms-1">Default</span>
                                    @endif
                                </div>
                                <div class="small text-muted">{{ $address->oneLine() }}</div>
                            </div>
                            <div class="d-flex gap-2 align-items-start">
                                @unless ($address->is_default)
                                    <form method="POST" action="{{ route('store.account.addresses.default', $address) }}">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-outline-secondary font-heading">Set Default</button>
                                    </form>
                                @endunless
                                <form method="POST" action="{{ route('store.account.addresses.destroy', $address) }}" onsubmit="return confirm('Remove this address?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger font-heading">Remove</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted font-heading small mb-4">No saved addresses yet. Add one below or save during checkout.</p>
                    @endforelse

                    <h6 class="font-heading fw-bold text-dark mb-3">Add Address</h6>
                    @if ($errors->hasAny(['label', 'address_line', 'city', 'pincode']))
                        <div class="alert alert-danger border-0 rounded-3 small font-heading mb-3">
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
                                <label for="label" class="form-label font-heading small fw-semibold">Label</label>
                                <input type="text" id="label" name="label" value="{{ old('label') }}" class="form-control font-heading" maxlength="50" placeholder="Home">
                            </div>
                            <div class="col-md-8">
                                <label for="address_line" class="form-label font-heading small fw-semibold">Street Address</label>
                                <input type="text" id="address_line" name="address_line" value="{{ old('address_line') }}" class="form-control font-heading" required maxlength="2000">
                            </div>
                            <div class="col-md-6">
                                <label for="city" class="form-label font-heading small fw-semibold">City</label>
                                <input type="text" id="city" name="city" value="{{ old('city') }}" class="form-control font-heading" required maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label for="pincode" class="form-label font-heading small fw-semibold">Pincode</label>
                                <input type="text" id="pincode" name="pincode" value="{{ old('pincode') }}" class="form-control font-heading" required maxlength="10">
                            </div>
                            <div class="col-12">
                                <div class="form-check mb-2">
                                    <input type="hidden" name="is_default" value="0">
                                    <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1" @checked(old('is_default'))>
                                    <label class="form-check-label font-heading small" for="is_default">Make default</label>
                                </div>
                                <button type="submit" class="btn btn-primary-luxury font-heading fw-bold px-4">Save Address</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-12 col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h5 class="font-heading fw-bold text-dark pb-3 border-bottom mb-3">
                        <i class="fas fa-lock text-amber me-2"></i> Change Password
                    </h5>

                    @if ($errors->hasAny(['current_password', 'password']))
                        <div class="alert alert-danger border-0 rounded-3 small font-heading mb-3">
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
                            <label for="current_password" class="form-label font-heading small fw-semibold">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="form-control form-control-lg font-heading @error('current_password') is-invalid @enderror" required autocomplete="current-password">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label font-heading small fw-semibold">New Password</label>
                            <input type="password" id="password" name="password" class="form-control form-control-lg font-heading @error('password') is-invalid @enderror" required autocomplete="new-password">
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label font-heading small fw-semibold">Confirm New Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control form-control-lg font-heading" required autocomplete="new-password">
                        </div>
                        <button type="submit" class="btn btn-outline-secondary font-heading fw-semibold px-4">
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
