<div class="col-md-4">
    <label for="{{ $statusId }}" class="form-label required">Status</label>
    <select class="form-select @error('status') is-invalid @enderror" id="{{ $statusId }}" name="status" required>
        <option value="active" @selected(old('status', $record->status) === 'active')>Active</option>
        <option value="inactive" @selected(old('status', $record->status) === 'inactive')>Inactive</option>
    </select>
    @error('status')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
