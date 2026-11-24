@php
    $isEdit = $catalogProductCategory->exists;
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.catalog-product-categories.update', $catalogProductCategory) : route('admin.catalog-product-categories.store') }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-md-8">
            <label for="catalog_room_id" class="form-label required">Room</label>
            <select class="form-select @error('catalog_room_id') is-invalid @enderror" id="catalog_room_id" name="catalog_room_id" required>
                <option value="">Select</option>
                @foreach ($catalogRooms as $option)
                    <option value="{{ $option->id }}" @selected((string) old('catalog_room_id', $catalogProductCategory->catalog_room_id) === (string) $option->id)>{{ $option->name }}</option>
                @endforeach
            </select>
            @error('catalog_room_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-8">
            <label for="name" class="form-label required">Name</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $catalogProductCategory->name) }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="slug" class="form-label">Slug</label>
            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $catalogProductCategory->slug) }}" placeholder="optional">
            @error('slug')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="sort_order" class="form-label">Sort Order</label>
            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $catalogProductCategory->sort_order ?? 0) }}" min="0" step="1">
            @error('sort_order')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        @include('admin.partials.record-status', ['statusId' => 'catalog_product_category_record_status', 'record' => $catalogProductCategory])
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-success">Save Product Category</button>
        <a href="{{ route('admin.catalog-product-categories.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>