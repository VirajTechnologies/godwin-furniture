@php
    $isEdit = $city->exists;
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.cities.update', $city) : route('admin.cities.store') }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-md-4">
            <label for="state_id" class="form-label">State</label>
            <select class="form-select @error('state_id') is-invalid @enderror" id="state_id" name="state_id" required>
                <option value="">Select State</option>
                @foreach ($states as $state)
                    <option value="{{ $state->id }}" @selected((string) old('state_id', $city->state_id) === (string) $state->id)>{{ $state->state_name }}</option>
                @endforeach
            </select>
            @error('state_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="district_id" class="form-label">District</label>
            <select class="form-select @error('district_id') is-invalid @enderror" id="district_id" name="district_id" required>
                <option value="">Select District</option>
                @foreach ($districts as $district)
                    <option value="{{ $district->id }}" @selected((string) old('district_id', $city->district_id) === (string) $district->id)>{{ $district->district_name }}</option>
                @endforeach
            </select>
            @error('district_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="city_name" class="form-label">City Name</label>
            <input type="text" class="form-control @error('city_name') is-invalid @enderror" id="city_name" name="city_name" value="{{ old('city_name', $city->city_name) }}" required>
            @error('city_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        @include('admin.partials.record-status', ['statusId' => 'city_record_status', 'record' => $city])
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-success">Save city</button>
        <a href="{{ route('admin.cities.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>

@include('admin.partials.location-filters')
