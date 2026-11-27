@extends('layouts.store')

@section('title', 'Customer Sign In')
@section('body_class', 'gw-theme-body')

@section('content')
    @include('store.partials.header')

    <section class="gw-auth-shell">
        <div class="gw-auth-card">
            <div class="text-center mb-4">
                <a href="{{ route('store.home') }}" class="d-inline-block mb-3">
                    <img src="{{ asset('store/images/logo.png') }}" alt="GODWIN" style="height: 40px; max-width: 190px; object-fit: contain;">
                </a>
                <h1 class="gw-page-title" style="font-size: 2rem !important;">Welcome Back</h1>
                <p class="gw-page-sub">Sign in to place orders and track deliveries</p>
            </div>

            @if ($errors->any())
                <div class="gw-alert gw-alert-danger mb-3">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('store.login.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="email" class="gw-label required">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="gw-input @error('email') is-invalid @enderror" placeholder="name@example.com" required autocomplete="email">
                </div>

                <div class="mb-3">
                    <label for="password" class="gw-label required">Password</label>
                    <div class="position-relative auth-pass-inputgroup">
                        <input type="password" id="password" name="password" class="gw-input pe-5 password-input @error('password') is-invalid @enderror" required autocomplete="current-password">
                        <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" aria-label="Show password" style="height: 100%; padding-inline: .75rem;">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-check mb-4">
                    <input type="checkbox" class="form-check-input gw-check-input" id="remember" name="remember" value="1" @checked(old('remember'))>
                    <label class="form-check-label small" for="remember" style="color: var(--ink);">Keep me signed in on this device</label>
                </div>

                <button type="submit" class="gw-btn w-100 text-center py-3 mb-3">
                    <i class="fas fa-sign-in-alt me-2"></i> Sign In
                </button>
            </form>

            <div class="text-center pt-3 mt-3" style="border-top: 1px solid var(--stone);">
                <span class="small" style="color: var(--mut);">
                    Don't have an account?
                    <a href="{{ route('store.register') }}" class="gw-view-link ms-1">Register Here</a>
                </span>
            </div>
        </div>
    </section>

    @include('store.partials.footer')
@endsection

@push('scripts')
<script src="{{ vasset('assets/js/pages/password-addon.init.js') }}"></script>
@endpush
