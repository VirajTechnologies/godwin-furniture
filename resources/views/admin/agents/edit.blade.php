@extends('layouts.admin')

@section('title', 'Edit Agent')
@section('page_title', $agent->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.agents.index') }}">Agents</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">{{ $agent->name }}</h5></div>
                <div class="card-body">@include('admin.agents._form')</div>
            </div>
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Cashiers</h5></div>
                <div class="card-body">
                    <p class="text-muted">A cashier can sell and see stock for this shop. Receiving transfers and stock requests stay with the manager.</p>
                    @if ($agent->cashiers->isEmpty())
                        <p class="mb-4">No cashiers yet.</p>
                    @else
                        <div class="table-responsive mb-4">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($agent->cashiers as $cashier)
                                        <tr>
                                            <td>{{ $cashier->name }}</td>
                                            <td>{{ $cashier->email }}</td>
                                            <td>{{ $cashier->phone ?: '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('admin.agents.cashiers.store', $agent) }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="cashier_name" class="form-label">Name</label>
                                <input type="text" class="form-control @error('cashier_name') is-invalid @enderror" id="cashier_name" name="cashier_name" value="{{ old('cashier_name') }}" required>
                                @error('cashier_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="cashier_email" class="form-label">Login Email</label>
                                <input type="email" class="form-control @error('cashier_email') is-invalid @enderror" id="cashier_email" name="cashier_email" value="{{ old('cashier_email') }}" required>
                                @error('cashier_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <label for="cashier_phone" class="form-label">Phone</label>
                                <input type="text" class="form-control @error('cashier_phone') is-invalid @enderror" id="cashier_phone" name="cashier_phone" value="{{ old('cashier_phone') }}">
                                @error('cashier_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <label for="cashier_password" class="form-label">Password</label>
                                <input type="password" class="form-control @error('cashier_password') is-invalid @enderror" id="cashier_password" name="cashier_password" required autocomplete="new-password">
                                @error('cashier_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label d-block">&nbsp;</label>
                                <button type="submit" class="btn btn-success">Add Cashier</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
