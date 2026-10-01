@extends('layouts.store')

@section('title', 'Customer Sign In')
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
        .login-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 440px;
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
    <div class="login-card p-4 p-sm-5 my-4">
        <div class="text-center mb-4">
            <a href="{{ route('store.home') }}" class="d-inline-block mb-3">
                <img src="{{ asset('store/images/logo.png') }}" alt="Godwin Groups" style="height: 48px; max-width: 220px; object-fit: contain;">
            </a>
            <h4 class="fw-bold text-dark m-0">Welcome Back</h4>
            <p class="small text-muted m-0">Sign in to your Godwin priority client account</p>
        </div>

        <!-- Preset Quick Chip -->
        <div class="p-2.5 bg-light rounded-3 border mb-4 text-center">
            <span class="small text-muted d-block mb-1 font-heading fw-semibold">Quick Demo Login:</span>
            <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 font-heading fw-semibold" onclick="document.getElementById('email').value='client@godwin-groups.com'; document.getElementById('pass').value='GodwinClient@2026';">
                👤 Fill Demo Client Credentials
            </button>
        </div>

        <form action="{{ route('store.home') }}" method="GET">
            <div class="mb-3">
                <label class="font-heading fw-bold small text-dark mb-1">Email Address</label>
                <input type="email" id="email" class="form-control font-heading py-2.5 shadow-none" placeholder="name@godwin-groups.com" value="client@godwin-groups.com" required>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="font-heading fw-bold small text-dark m-0">Password</label>
                    <a href="#" class="small text-amber text-decoration-none fw-semibold" style="color: #C2652B;">Forgot Password?</a>
                </div>
                <input type="password" id="pass" class="form-control font-heading py-2.5 shadow-none" value="GodwinClient@2026" required>
            </div>

            <div class="form-check mb-4">
                <input type="checkbox" class="form-check-input" id="remember" checked>
                <label class="form-check-label small font-heading text-dark" for="remember">Keep me signed in on this device</label>
            </div>

            <button type="submit" class="btn btn-amber w-100 py-3 rounded-3 font-heading fw-bold fs-6 mb-3 shadow-sm"><i class="fas fa-sign-in-alt me-2"></i> Sign In To Account</button>
        </form>

        <div class="text-center border-top pt-3 mt-3">
            <span class="small text-muted">Don't have an account? <a href="{{ route('store.register') }}" class="fw-bold text-decoration-none" style="color: #C2652B;">Register Here</a></span>
        </div>
    </div>


@endsection
