@extends('layouts.store')

@section('title', 'Create Account')
@section('body_class', '')

@push('styles')
<style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 520px;
        }
        .btn-amber {
            background-color: #C2652B;
            color: #ffffff;
            border: none;
            transition: all 0.2s ease;
        }
        .btn-amber:hover {
            background-color: #A35220;
            color: #ffffff;
        }
</style>
@endpush
@section('content')
    <div class="register-card p-4 p-sm-5 my-4">
        <div class="text-center mb-4">
            <a href="{{ route('store.home') }}" class="d-inline-block mb-3">
                <img src="{{ asset('store/images/logo.png') }}" alt="Godwin Groups" style="height: 48px; max-width: 220px; object-fit: contain;">
            </a>
            <h4 class="fw-bold text-dark m-0">Create Account</h4>
            <p class="small text-muted m-0">Register once, then sign in to checkout anytime</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger border-0 rounded-3 small font-heading mb-3">
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
                    <label for="name" class="font-heading fw-bold small text-dark mb-1 required">Full Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control font-heading py-2 shadow-none @error('name') is-invalid @enderror" required maxlength="150" autocomplete="name">
                </div>
                <div class="col-md-6">
                    <label for="phone" class="font-heading fw-bold small text-dark mb-1 required">Phone Number</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="form-control font-heading py-2 shadow-none @error('phone') is-invalid @enderror" required maxlength="20" autocomplete="tel">
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="font-heading fw-bold small text-dark mb-1 required">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control font-heading py-2 shadow-none @error('email') is-invalid @enderror" required maxlength="150" autocomplete="email">
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="password" class="font-heading fw-bold small text-dark mb-1 required">Create Password</label>
                    <input type="password" id="password" name="password" class="form-control font-heading py-2 shadow-none @error('password') is-invalid @enderror" required autocomplete="new-password">
                </div>
                <div class="col-md-6">
                    <label for="password_confirmation" class="font-heading fw-bold small text-dark mb-1 required">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control font-heading py-2 shadow-none" required autocomplete="new-password">
                </div>
            </div>

            <button type="submit" class="btn btn-amber w-100 py-3 rounded-3 font-heading fw-bold fs-6 mb-3 shadow-sm"><i class="fas fa-user-plus me-2"></i> Register Account</button>
        </form>

        <div class="text-center border-top pt-3 mt-3">
            <span class="small text-muted">Already have a Godwin account? <a href="{{ route('store.login') }}" class="fw-bold text-decoration-none" style="color: #C2652B;">Sign In Here</a></span>
        </div>
    </div>
@endsection
