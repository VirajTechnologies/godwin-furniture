@php
    $isEdit = $employee->exists;
    $user = $employee->user;
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.employees.update', $employee) : route('admin.employees.store') }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-md-4">
            <label for="employee_code" class="form-label">Employee Code</label>
            @if ($isEdit)
                <input type="text" class="form-control" id="employee_code" value="{{ $employee->employee_code }}" readonly>
            @else
                <input type="text" class="form-control @error('employee_code') is-invalid @enderror" id="employee_code" name="employee_code" value="{{ old('employee_code') }}" placeholder="EMP001" required>
                @error('employee_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            @endif
        </div>
        <div class="col-md-8">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user?->name) }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user?->email) }}" required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user?->phone) }}">
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="designation" class="form-label">Designation</label>
            <input type="text" class="form-control @error('designation') is-invalid @enderror" id="designation" name="designation" value="{{ old('designation', $employee->designation) }}">
            @error('designation')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" @required(! $isEdit) autocomplete="new-password">
            @if ($isEdit)
                <div class="form-text">Leave blank to keep the current password.</div>
            @endif
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" @required(! $isEdit) autocomplete="new-password">
        </div>
        <div class="col-md-4">
            <label for="role_id" class="form-label">Role</label>
            <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                <option value="">Select Role</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" data-requires-warehouse="{{ $role->slug === 'warehouse_staff' ? '1' : '0' }}" data-requires-branch="{{ in_array($role->slug, ['branch_manager', 'branch_staff'], true) ? '1' : '0' }}" @selected((string) old('role_id', $user?->role_id) === (string) $role->id)>{{ $role->name }}</option>
                @endforeach
            </select>
            @error('role_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="warehouse_id" class="form-label">Warehouse</label>
            <select class="form-select @error('warehouse_id') is-invalid @enderror" id="warehouse_id" name="warehouse_id">
                <option value="">Select Warehouse</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" @selected((string) old('warehouse_id', $employee->warehouse_id) === (string) $warehouse->id)>{{ $warehouse->name }}</option>
                @endforeach
            </select>
            <div class="form-text">Required for warehouse staff.</div>
            @error('warehouse_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="branch_id" class="form-label">Branch</label>
            <select class="form-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id">
                <option value="">Select Branch</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected((string) old('branch_id', $employee->branch_id) === (string) $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
            <div class="form-text">Required for branch staff.</div>
            @error('branch_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        @include('admin.partials.record-status', ['statusId' => 'employee_record_status', 'record' => $employee])
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-success">Save Employee</button>
        <a href="{{ route('admin.employees.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roleSelect = document.getElementById('role_id');
            const warehouseSelect = document.getElementById('warehouse_id');
            const branchSelect = document.getElementById('branch_id');

            function syncPlace() {
                const option = roleSelect.options[roleSelect.selectedIndex];
                warehouseSelect.required = option && option.dataset.requiresWarehouse === '1';
                branchSelect.required = option && option.dataset.requiresBranch === '1';
            }

            roleSelect.addEventListener('change', syncPlace);
            syncPlace();
        });
    </script>
@endpush
