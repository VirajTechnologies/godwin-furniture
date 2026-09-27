@php
    $isEdit = $state->exists;
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.states.update', $state) : route('admin.states.store') }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-md-8">
            <label for="state_name" class="form-label">State Name</label>
            <input type="text" class="form-control @error('state_name') is-invalid @enderror" id="state_name" name="state_name" value="{{ old('state_name', $state->state_name) }}" required>
            @error('state_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        @include('admin.partials.record-status', ['statusId' => 'state_record_status', 'record' => $state])
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-success">Save state</button>
        <a href="{{ route('admin.states.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>
