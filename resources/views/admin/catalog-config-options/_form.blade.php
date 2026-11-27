@php
    $isEdit = $catalogConfigOption->exists;
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.catalog-config-options.update', $catalogConfigOption) : route('admin.catalog-config-options.store') }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-md-4">
            <label for="catalog_config_option_family_key" class="form-label required">Family</label>
            <select class="form-select @error('family_key') is-invalid @enderror" id="catalog_config_option_family_key" name="family_key" required>
                @foreach ($families as $key => $family)
                    <option value="{{ $key }}" @selected(old('family_key', $catalogConfigOption->family_key) === $key)>{{ $family['label'] ?? $key }} ({{ $key }})</option>
                @endforeach
            </select>
            @error('family_key')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="catalog_config_option_field_key" class="form-label required">Field</label>
            <select class="form-select @error('field_key') is-invalid @enderror" id="catalog_config_option_field_key" name="field_key" required>
                <option value="">Select</option>
            </select>
            @error('field_key')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-8">
            <label for="catalog_config_option_name" class="form-label required">Option name</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="catalog_config_option_name" name="name" value="{{ old('name', $catalogConfigOption->name) }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="catalog_config_option_sort_order" class="form-label">Sort Order</label>
            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="catalog_config_option_sort_order" name="sort_order" value="{{ old('sort_order', $catalogConfigOption->sort_order ?? 0) }}" min="0" step="1">
            @error('sort_order')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        @include('admin.partials.record-status', ['statusId' => 'catalog_config_option_record_status', 'record' => $catalogConfigOption])
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-success">Save Option</button>
        <a href="{{ route('admin.catalog-config-options.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>

@push('scripts')
<script>
    (function () {
        const fieldsByFamily = @json($selectFieldsByFamily);
        const familySelect = document.getElementById('catalog_config_option_family_key');
        const fieldSelect = document.getElementById('catalog_config_option_field_key');
        const selectedField = @json(old('field_key', $catalogConfigOption->field_key));

        function refreshFields() {
            const family = familySelect.value;
            const fields = fieldsByFamily[family] || {};
            fieldSelect.innerHTML = '<option value="">Select</option>';
            Object.keys(fields).forEach(function (key) {
                const option = document.createElement('option');
                option.value = key;
                option.textContent = fields[key];
                if (key === selectedField) {
                    option.selected = true;
                }
                fieldSelect.appendChild(option);
            });
        }

        familySelect.addEventListener('change', function () {
            refreshFields();
        });
        refreshFields();
    })();
</script>
@endpush
