@php
    $isEdit = $productUpholstery->exists;
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.product-upholsteries.update', $productUpholstery) : route('admin.product-upholsteries.store') }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-md-8">
            <label for="productUpholstery_name" class="form-label required">Name</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="productUpholstery_name" name="name" value="{{ old('name', $productUpholstery->name) }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label for="productUpholstery_sort_order" class="form-label">Sort Order</label>
            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="productUpholstery_sort_order" name="sort_order" value="{{ old('sort_order', $productUpholstery->sort_order ?? 0) }}" min="0" step="1">
            @error('sort_order')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        @include('admin.partials.record-status', ['statusId' => 'productUpholstery_record_status', 'record' => $productUpholstery])
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-success">Save Upholstery</button>
        <a href="{{ route('admin.product-upholsteries.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>