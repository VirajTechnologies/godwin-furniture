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
                        @foreach ($agent->cashiers as $cashier)
                            @php
                                $editingCashier = old('cashier_form') === 'update' && (string) old('cashier_id') === (string) $cashier->id;
                            @endphp
                            <form method="POST" action="{{ route('admin.agents.cashiers.update', [$agent, $cashier]) }}" class="border rounded p-3 mb-3">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="cashier_form" value="update">
                                <input type="hidden" name="cashier_id" value="{{ $cashier->id }}">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-3">
                                        <label for="cashier_name_{{ $cashier->id }}" class="form-label">Name</label>
                                        <input type="text" class="form-control @if ($editingCashier) @error('cashier_name') is-invalid @enderror @endif" id="cashier_name_{{ $cashier->id }}" name="cashier_name" value="{{ $editingCashier ? old('cashier_name', $cashier->name) : $cashier->name }}" required>
                                        @if ($editingCashier)
                                            @error('cashier_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        @endif
                                    </div>
                                    <div class="col-md-3">
                                        <label for="cashier_email_{{ $cashier->id }}" class="form-label">Login Email</label>
                                        <input type="email" class="form-control @if ($editingCashier) @error('cashier_email') is-invalid @enderror @endif" id="cashier_email_{{ $cashier->id }}" name="cashier_email" value="{{ $editingCashier ? old('cashier_email', $cashier->email) : $cashier->email }}" required>
                                        @if ($editingCashier)
                                            @error('cashier_email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        @endif
                                    </div>
                                    <div class="col-md-2">
                                        <label for="cashier_phone_{{ $cashier->id }}" class="form-label">Phone</label>
                                        <input type="text" class="form-control @if ($editingCashier) @error('cashier_phone') is-invalid @enderror @endif" id="cashier_phone_{{ $cashier->id }}" name="cashier_phone" value="{{ $editingCashier ? old('cashier_phone', $cashier->phone) : $cashier->phone }}">
                                        @if ($editingCashier)
                                            @error('cashier_phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        @endif
                                    </div>
                                    <div class="col-md-2">
                                        <label for="cashier_password_{{ $cashier->id }}" class="form-label">Password</label>
                                        <input type="password" class="form-control @if ($editingCashier) @error('cashier_password') is-invalid @enderror @endif" id="cashier_password_{{ $cashier->id }}" name="cashier_password" autocomplete="new-password" placeholder="Unchanged">
                                        @if ($editingCashier)
                                            @error('cashier_password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        @endif
                                    </div>
                                    <div class="col-md-2">
                                        <label for="cashier_status_{{ $cashier->id }}" class="form-label">Status</label>
                                        <select class="form-select @if ($editingCashier) @error('cashier_status') is-invalid @enderror @endif" id="cashier_status_{{ $cashier->id }}" name="cashier_status" required>
                                            <option value="active" @selected(($editingCashier ? old('cashier_status', $cashier->status) : $cashier->status) === 'active')>Active</option>
                                            <option value="inactive" @selected(($editingCashier ? old('cashier_status', $cashier->status) : $cashier->status) === 'inactive')>Inactive</option>
                                        </select>
                                        @if ($editingCashier)
                                            @error('cashier_status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        @endif
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">Save Cashier</button>
                                        <span class="text-muted ms-2">Leave the password blank to keep the current one. An inactive cashier cannot sign in.</span>
                                    </div>
                                </div>
                            </form>
                        @endforeach
                    @endif
                    @php $addingCashier = old('cashier_form') === 'create'; @endphp
                    <form method="POST" action="{{ route('admin.agents.cashiers.store', $agent) }}">
                        @csrf
                        <input type="hidden" name="cashier_form" value="create">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="cashier_name" class="form-label">Name</label>
                                <input type="text" class="form-control @if ($addingCashier) @error('cashier_name') is-invalid @enderror @endif" id="cashier_name" name="cashier_name" value="{{ $addingCashier ? old('cashier_name') : '' }}" required>
                                @if ($addingCashier)
                                    @error('cashier_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>
                            <div class="col-md-3">
                                <label for="cashier_email" class="form-label">Login Email</label>
                                <input type="email" class="form-control @if ($addingCashier) @error('cashier_email') is-invalid @enderror @endif" id="cashier_email" name="cashier_email" value="{{ $addingCashier ? old('cashier_email') : '' }}" required>
                                @if ($addingCashier)
                                    @error('cashier_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>
                            <div class="col-md-2">
                                <label for="cashier_phone" class="form-label">Phone</label>
                                <input type="text" class="form-control @if ($addingCashier) @error('cashier_phone') is-invalid @enderror @endif" id="cashier_phone" name="cashier_phone" value="{{ $addingCashier ? old('cashier_phone') : '' }}">
                                @if ($addingCashier)
                                    @error('cashier_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>
                            <div class="col-md-2">
                                <label for="cashier_password" class="form-label">Password</label>
                                <input type="password" class="form-control @if ($addingCashier) @error('cashier_password') is-invalid @enderror @endif" id="cashier_password" name="cashier_password" required autocomplete="new-password">
                                @if ($addingCashier)
                                    @error('cashier_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @endif
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
