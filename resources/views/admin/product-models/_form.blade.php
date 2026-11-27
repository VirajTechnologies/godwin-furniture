@php
    $isEdit = $productModel->exists;
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.product-models.update', $productModel) : route('admin.product-models.store') }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-md-8">
            <label for="catalog_product_category_id" class="form-label required">Product Category</label>
            <select class="form-select @error('catalog_product_category_id') is-invalid @enderror" id="catalog_product_category_id" name="catalog_product_category_id" required>
                <option value="">Select</option>
                @foreach ($catalogProductCategories as $option)
                    <option value="{{ $option->id }}" @selected((string) old('catalog_product_category_id', $productModel->catalog_product_category_id) === (string) $option->id)>{{ $option->name }}</option>
                @endforeach
            </select>
            @error('catalog_product_category_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-8">
            <label for="name" class="form-label required">Name</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $productModel->name) }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="sort_order" class="form-label">Sort Order</label>
            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $productModel->sort_order ?? 0) }}" min="0" step="1">
            @error('sort_order')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        @include('admin.partials.record-status', ['statusId' => 'product_model_record_status', 'record' => $productModel])
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-success">Save Product Model</button>
        <a href="{{ route('admin.product-models.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>