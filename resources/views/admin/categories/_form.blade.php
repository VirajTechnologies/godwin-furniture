@php
    $isEdit = $category->exists;
    $selectedParent = (string) old('parent_id', $category->parent_id);
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-md-4">
            <label for="parent_id" class="form-label">Parent Category</label>
            <select class="form-select @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
                <option value="">None (This Is A Category)</option>
                @foreach ($rooms as $room)
                    <option value="{{ $room->id }}" @selected($selectedParent === (string) $room->id)>{{ $room->name }}</option>
                @endforeach
            </select>
            <div class="form-text">Leave empty for a top-level category such as Living Room or Bedroom.</div>
            @error('parent_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-8">
            <label for="name" class="form-label required">Category Name</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $category->name) }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="slug" class="form-label">Slug</label>
            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $category->slug) }}" placeholder="living-room">
            <div class="form-text">Used in store URLs. Leave blank to build from the name.</div>
            @error('slug')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="sort_order" class="form-label">Sort Order</label>
            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" min="0" step="1">
            <div class="form-text">Lower numbers appear first in the store menu.</div>
            @error('sort_order')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-8">
            <label for="image_url" class="form-label">Image Url</label>
            <input type="text" class="form-control @error('image_url') is-invalid @enderror" id="image_url" name="image_url" value="{{ old('image_url', $category->image_url) }}">
            @error('image_url')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        @include('admin.partials.record-status', ['statusId' => 'category_record_status', 'record' => $category])
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-success">Save Category</button>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>
