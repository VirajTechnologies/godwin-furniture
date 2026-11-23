@extends('layouts.store')

@section('title', 'Create Account')
@section('body_class', 'gw-theme-body')

@section('content')
    @include('store.partials.header')

    <section class="gw-auth-shell">
        <div class="gw-auth-card gw-auth-wide">
            <div class="text-center mb-4">
                <a href="{{ route('store.home') }}" class="d-inline-block mb-3">
                    <img src="{{ asset('store/images/logo.png') }}" alt="GODWIN" style="height: 40px; max-width: 190px; object-fit: contain;">
                </a>
                <h1 class="gw-page-title" style="font-size: 2rem !important;">Create Account</h1>
                <p class="gw-page-sub">Register once, then sign in to checkout anytime</p>
            </div>

            @if ($errors->any())
                <div class="gw-alert gw-alert-danger mb-3">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('store.register.store') }}" method="POST">
                @csrf
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="name" class="gw-label required">Full Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" class="gw-input @error('name') is-invalid @enderror" required maxlength="150" autocomplete="name">
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="gw-label required">Phone Number</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="gw-input @error('phone') is-invalid @enderror" required maxlength="20" autocomplete="tel">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="gw-label required">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="gw-input @error('email') is-invalid @enderror" required maxlength="150" autocomplete="email">
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="password" class="gw-label required">Create Password</label>
                        <input type="password" id="password" name="password" class="gw-input @error('password') is-invalid @enderror" required autocomplete="new-password">
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="gw-label required">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="gw-input" required autocomplete="new-password">
                    </div>
                </div>

                <button type="submit" class="gw-btn w-100 text-center py-3 mb-3">
                    <i class="fas fa-user-plus me-2"></i> Register Account
                </button>
            </form>

            <div class="text-center pt-3 mt-3" style="border-top: 1px solid var(--stone);">
                <span class="small" style="color: var(--mut);">
                    Already have a Godwin account?
                    <a href="{{ route('store.login') }}" class="gw-view-link ms-1">Sign In Here</a>
                </span>
            </div>
        </div>
    </section>

    @include('store.partials.footer')
@endsection
