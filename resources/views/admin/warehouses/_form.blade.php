@php
    $isEdit = $warehouse->exists;
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.warehouses.update', $warehouse) : route('admin.warehouses.store') }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-md-4">
            <label for="code" class="form-label">Code</label>
            @if ($isEdit)
                <input type="text" class="form-control" id="code" value="{{ $warehouse->code }}" readonly>
            @else
                <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" placeholder="WH001" required>
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            @endif
        </div>
        <div class="col-md-8">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $warehouse->name) }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="contact_person" class="form-label">Contact Person</label>
            <input type="text" class="form-control @error('contact_person') is-invalid @enderror" id="contact_person" name="contact_person" value="{{ old('contact_person', $warehouse->contact_person) }}">
            @error('contact_person')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $warehouse->phone) }}">
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $warehouse->email) }}">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12">
            <label for="address_line" class="form-label">Address</label>
            <textarea class="form-control @error('address_line') is-invalid @enderror" id="address_line" name="address_line" rows="3" required>{{ old('address_line', $warehouse->address_line) }}</textarea>
            @error('address_line')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3">
            <label for="state_id" class="form-label">State</label>
            <select class="form-select @error('state_id') is-invalid @enderror" id="state_id" name="state_id" required>
                <option value="">Select State</option>
                @foreach ($states as $state)
                    <option value="{{ $state->id }}" @selected((string) old('state_id', $warehouse->state_id) === (string) $state->id)>{{ $state->state_name }}</option>
                @endforeach
            </select>
            @error('state_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3">
            <label for="district_id" class="form-label">District</label>
            <select class="form-select @error('district_id') is-invalid @enderror" id="district_id" name="district_id" required>
                <option value="">Select District</option>
                @foreach ($districts as $district)
                    <option value="{{ $district->id }}" @selected((string) old('district_id', $warehouse->district_id) === (string) $district->id)>{{ $district->district_name }}</option>
                @endforeach
            </select>
            @error('district_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3">
            <label for="city_id" class="form-label">City</label>
            <select class="form-select @error('city_id') is-invalid @enderror" id="city_id" name="city_id" required>
                <option value="">Select City</option>
                @foreach ($cities as $city)
                    <option value="{{ $city->id }}" @selected((string) old('city_id', $warehouse->city_id) === (string) $city->id)>{{ $city->city_name }}</option>
                @endforeach
            </select>
            @error('city_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3">
            <label for="pincode" class="form-label">Pincode</label>
            <input type="text" class="form-control @error('pincode') is-invalid @enderror" id="pincode" name="pincode" value="{{ old('pincode', $warehouse->pincode) }}" required>
            @error('pincode')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="warehouse_status" class="form-label">Status</label>
            <select class="form-select @error('status') is-invalid @enderror" id="warehouse_status" name="status" required>
                <option value="active" @selected(old('status', $warehouse->status) === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $warehouse->status) === 'inactive')>Inactive</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $warehouse->notes) }}</textarea>
            @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-success">Save warehouse</button>
        <a href="{{ route('admin.warehouses.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>

@include('admin.partials.location-filters')
