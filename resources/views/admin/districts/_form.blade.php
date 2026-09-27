@php
    $isEdit = $district->exists;
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.districts.update', $district) : route('admin.districts.store') }}">
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
                    <option value="{{ $state->id }}" @selected((string) old('state_id', $district->state_id) === (string) $state->id)>{{ $state->state_name }}</option>
                @endforeach
            </select>
            @error('state_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-8">
            <label for="district_name" class="form-label">District Name</label>
            <input type="text" class="form-control @error('district_name') is-invalid @enderror" id="district_name" name="district_name" value="{{ old('district_name', $district->district_name) }}" required>
            @error('district_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        @include('admin.partials.record-status', ['statusId' => 'district_record_status', 'record' => $district])
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-success">Save district</button>
        <a href="{{ route('admin.districts.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>
