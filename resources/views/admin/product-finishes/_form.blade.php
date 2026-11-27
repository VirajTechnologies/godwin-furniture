@php
    $isEdit = $productFinish->exists;
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.product-finishes.update', $productFinish) : route('admin.product-finishes.store') }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-md-8">
            <label for="productFinish_name" class="form-label required">Name</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="productFinish_name" name="name" value="{{ old('name', $productFinish->name) }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label for="productFinish_sort_order" class="form-label">Sort Order</label>
            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="productFinish_sort_order" name="sort_order" value="{{ old('sort_order', $productFinish->sort_order ?? 0) }}" min="0" step="1">
            @error('sort_order')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        @include('admin.partials.record-status', ['statusId' => 'productFinish_record_status', 'record' => $productFinish])
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-success">Save Finish</button>
        <a href="{{ route('admin.product-finishes.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>