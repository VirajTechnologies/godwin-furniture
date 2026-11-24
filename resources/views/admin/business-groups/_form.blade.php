@php
    $isEdit = $businessGroup->exists;
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.business-groups.update', $businessGroup) : route('admin.business-groups.store') }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-md-8">
            <label for="name" class="form-label required">Name</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $businessGroup->name) }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="sort_order" class="form-label">Sort Order</label>
            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $businessGroup->sort_order ?? 0) }}" min="0" step="1">
            @error('sort_order')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        @include('admin.partials.record-status', ['statusId' => 'business_group_record_status', 'record' => $businessGroup])
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-success">Save Business Group</button>
        <a href="{{ route('admin.business-groups.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>