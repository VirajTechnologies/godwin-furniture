@php
    $isEdit = $productColour->exists;
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.product-colours.update', $productColour) : route('admin.product-colours.store') }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-md-8">
            <label for="productColour_name" class="form-label required">Name</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="productColour_name" name="name" value="{{ old('name', $productColour->name) }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="hex" class="form-label required">Hex</label>
            <input type="color" class="form-control form-control-color @error('hex') is-invalid @enderror" id="hex" name="hex" value="{{ old('hex', $productColour->hex ?? '#888888') }}" title="Colour" required>
            @error('hex')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="productColour_sort_order" class="form-label">Sort Order</label>
            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="productColour_sort_order" name="sort_order" value="{{ old('sort_order', $productColour->sort_order ?? 0) }}" min="0" step="1">
            @error('sort_order')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        @include('admin.partials.record-status', ['statusId' => 'productColour_record_status', 'record' => $productColour])
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-success">Save Colour</button>
        <a href="{{ route('admin.product-colours.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>