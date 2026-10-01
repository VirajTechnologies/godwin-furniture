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
            <h4 class="fw-bold text-dark m-0">Create Priority Account</h4>
            <p class="small text-muted m-0">Unlock direct factory pricing & 10-year warranty registration</p>
        </div>

        <form action="{{ route('store.login') }}" method="GET">
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="font-heading fw-bold small text-dark mb-1">Full Name</label>
                    <input type="text" class="form-control font-heading py-2 shadow-none" placeholder="Vikram Godwin" required>
                </div>
                <div class="col-md-6">
                    <label class="font-heading fw-bold small text-dark mb-1">Phone Number</label>
                    <input type="text" class="form-control font-heading py-2 shadow-none" placeholder="+91 98230 11223" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="font-heading fw-bold small text-dark mb-1">Email Address</label>
                <input type="email" class="form-control font-heading py-2 shadow-none" placeholder="vikram@godwin-groups.com" required>
            </div>

            <div class="mb-3">
                <label class="font-heading fw-bold small text-dark mb-1">Account Category</label>
                <select class="form-select font-heading py-2 shadow-none">
                    <option selected>🏡 Individual Homeowner Client</option>
                    <option>📐 Architect / Interior Designer</option>
                    <option>🏢 Corporate / Executive Workplace</option>
                    <option>🏬 Authorized Retail Dealer</option>
                </select>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="font-heading fw-bold small text-dark mb-1">Create Password</label>
                    <input type="password" class="form-control font-heading py-2 shadow-none" placeholder="••••••••" required>
                </div>
                <div class="col-md-6">
                    <label class="font-heading fw-bold small text-dark mb-1">Confirm Password</label>
                    <input type="password" class="form-control font-heading py-2 shadow-none" placeholder="••••••••" required>
                </div>
            </div>

            <div class="form-check mb-4">
                <input type="checkbox" class="form-check-input" id="terms" checked required>
                <label class="form-check-label small font-heading text-dark" for="terms">I agree to Godwin's <a href="#" class="text-amber fw-semibold">Terms of Service</a> & <a href="#" class="text-amber fw-semibold">Priority Club Benefits</a></label>
            </div>

            <button type="submit" class="btn btn-amber w-100 py-3 rounded-3 font-heading fw-bold fs-6 mb-3 shadow-sm"><i class="fas fa-user-plus me-2"></i> Register Account</button>
        </form>

        <div class="text-center border-top pt-3 mt-3">
            <span class="small text-muted">Already have a Godwin account? <a href="{{ route('store.login') }}" class="fw-bold text-decoration-none" style="color: #C2652B;">Sign In Here</a></span>
        </div>
    </div>


@endsection
