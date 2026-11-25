@php
    $isEdit = $catalogRoom->exists;
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.catalog-rooms.update', $catalogRoom) : route('admin.catalog-rooms.store') }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-md-8">
            <label for="project_category_id" class="form-label required">Project</label>
            <select class="form-select @error('project_category_id') is-invalid @enderror" id="project_category_id" name="project_category_id" required>
                <option value="">Select</option>
                @foreach ($projectCategories as $option)
                    <option value="{{ $option->id }}" @selected((string) old('project_category_id', $catalogRoom->project_category_id) === (string) $option->id)>{{ $option->name }}</option>
                @endforeach
            </select>
            @error('project_category_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-8">
            <label for="name" class="form-label required">Name</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $catalogRoom->name) }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="slug" class="form-label">Slug</label>
            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $catalogRoom->slug) }}" placeholder="optional">
            @error('slug')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="sort_order" class="form-label">Sort Order</label>
            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $catalogRoom->sort_order ?? 0) }}" min="0" step="1">
            @error('sort_order')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-8">
            <div class="form-check mt-4">
                <input type="checkbox" class="form-check-input" id="show_on_store" name="show_on_store" value="1" @checked(old('show_on_store', $catalogRoom->show_on_store ?? true))>
                <label class="form-check-label" for="show_on_store">Show on ecommerce storefront</label>
            </div>
            <div class="form-text">Turn off for items available only in admin, branch, and agent (e.g. Kids Room).</div>
        </div>
        @include('admin.partials.record-status', ['statusId' => 'catalog_room_record_status', 'record' => $catalogRoom])
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-success">Save Room</button>
        <a href="{{ route('admin.catalog-rooms.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>