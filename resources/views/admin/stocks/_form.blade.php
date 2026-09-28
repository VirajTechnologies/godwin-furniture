@php
    $isEdit = $stock->exists;
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.stocks.update', $stock) : route('admin.stocks.store') }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label for="product_id" class="form-label">Product</label>
            @if ($isEdit)
                <input type="text" class="form-control" id="product_id" value="{{ $stock->product?->code }} · {{ $stock->product?->name }}" readonly>
            @else
                <select class="form-select @error('product_id') is-invalid @enderror" id="product_id" name="product_id" required>
                    <option value="">Select Product</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected((string) old('product_id') === (string) $product->id)>{{ $product->code }} · {{ $product->name }}</option>
                    @endforeach
                </select>
                @error('product_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            @endif
        </div>
        <div class="col-md-6">
            <label for="warehouse_id" class="form-label">Warehouse</label>
            @if ($isEdit)
                <input type="text" class="form-control" id="warehouse_id" value="{{ $stock->warehouse?->name }}" readonly>
            @else
                <select class="form-select @error('warehouse_id') is-invalid @enderror" id="warehouse_id" name="warehouse_id" required>
                    <option value="">Select Warehouse</option>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected((string) old('warehouse_id') === (string) $warehouse->id)>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
                @error('warehouse_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            @endif
        </div>
        <div class="col-md-4">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity', $stock->quantity) }}" min="0" step="1" required>
            <div class="form-text">This is the quantity at the warehouse. A branch receives stock only through a transfer.</div>
            @error('quantity')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12">
            <label for="note" class="form-label">Note</label>
            <textarea class="form-control @error('note') is-invalid @enderror" id="note" name="note" rows="3">{{ old('note') }}</textarea>
            @error('note')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-success">Save Stock</button>
        <a href="{{ route('admin.stocks.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>
