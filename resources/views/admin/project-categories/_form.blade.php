@php
    $isEdit = $projectCategory->exists;
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.project-categories.update', $projectCategory) : route('admin.project-categories.store') }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-md-8">
            <label for="business_group_id" class="form-label required">Business Group</label>
            <select class="form-select @error('business_group_id') is-invalid @enderror" id="business_group_id" name="business_group_id" required>
                <option value="">Select</option>
                @foreach ($businessGroups as $option)
                    <option value="{{ $option->id }}" @selected((string) old('business_group_id', $projectCategory->business_group_id) === (string) $option->id)>{{ $option->name }}</option>
                @endforeach
            </select>
            @error('business_group_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-8">
            <label for="name" class="form-label required">Name</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $projectCategory->name) }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="sort_order" class="form-label">Sort Order</label>
            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $projectCategory->sort_order ?? 0) }}" min="0" step="1">
            @error('sort_order')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-8">
            <div class="form-check mt-4">
                <input type="checkbox" class="form-check-input" id="show_on_store" name="show_on_store" value="1" @checked(old('show_on_store', $projectCategory->show_on_store ?? true))>
                <label class="form-check-label" for="show_on_store">Show on ecommerce store</label>
            </div>
            <div class="form-text">Uncheck for admin-only projects (e.g. Interiors).</div>
        </div>
        @include('admin.partials.record-status', ['statusId' => 'project_category_record_status', 'record' => $projectCategory])
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-success">Save Project</button>
        <a href="{{ route('admin.project-categories.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>