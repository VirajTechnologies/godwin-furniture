@php
    $isEdit = $category->exists;
    $selectedParent = (string) old('parent_id', $category->parent_id);
    $currentGroup = old('menu_group', $category->menu_group);
    $choiceOld = old('menu_group_choice');
    $roomGroups = $menuGroupsByRoom[$selectedParent] ?? [];
    $isKnownGroup = $currentGroup && in_array($currentGroup, $roomGroups, true);

    if ($choiceOld !== null) {
        $selectedChoice = $choiceOld;
    } elseif ($isKnownGroup) {
        $selectedChoice = $currentGroup;
    } elseif ($currentGroup) {
        $selectedChoice = '__new__';
    } else {
        $selectedChoice = '';
    }

    $customGroup = old('menu_group', $selectedChoice === '__new__' ? $currentGroup : '');
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
            <label for="name" class="form-label">Category Name</label>
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
        <div class="col-md-4" id="menu_group_wrap">
            <label for="menu_group_choice" class="form-label">Menu Group</label>
            <select class="form-select @error('menu_group_choice') is-invalid @enderror" id="menu_group_choice" name="menu_group_choice">
                <option value="">Select a group</option>
                <option value="__new__" @selected($selectedChoice === '__new__')>Add new group…</option>
            </select>
            <div class="form-text">Required for a subcategory. Reuse a column title or add a new one.</div>
            @error('menu_group_choice')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="mt-2 {{ $selectedChoice === '__new__' ? '' : 'd-none' }}" id="menu_group_custom_wrap">
                <label for="menu_group" class="form-label">New Group Name</label>
                <input type="text" class="form-control @error('menu_group') is-invalid @enderror" id="menu_group" name="menu_group" value="{{ $customGroup }}" placeholder="Sofas & Seating" maxlength="100">
                @error('menu_group')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-4">
            <label for="sort_order" class="form-label">Sort Order</label>
            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" min="0" step="1">
            <div class="form-text">Order of this subcategory inside its menu group.</div>
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const groupsByRoom = @json($menuGroupsByRoom);
            const parentSelect = document.getElementById('parent_id');
            const choiceSelect = document.getElementById('menu_group_choice');
            const wrap = document.getElementById('menu_group_wrap');
            const customWrap = document.getElementById('menu_group_custom_wrap');
            const customInput = document.getElementById('menu_group');
            const initialChoice = @json($selectedChoice);

            function rebuildChoices(keepValue) {
                const roomId = parentSelect.value;
                const groups = groupsByRoom[roomId] || [];
                const previous = keepValue ?? choiceSelect.value;

                choiceSelect.innerHTML = '';

                const blank = document.createElement('option');
                blank.value = '';
                blank.textContent = groups.length ? 'Select a group' : 'No groups yet — add one';
                choiceSelect.appendChild(blank);

                groups.forEach(function (name) {
                    const option = document.createElement('option');
                    option.value = name;
                    option.textContent = name;
                    choiceSelect.appendChild(option);
                });

                const addNew = document.createElement('option');
                addNew.value = '__new__';
                addNew.textContent = 'Add new group…';
                choiceSelect.appendChild(addNew);

                if (previous && [...choiceSelect.options].some(function (option) { return option.value === previous; })) {
                    choiceSelect.value = previous;
                } else if (!groups.length && roomId) {
                    choiceSelect.value = '__new__';
                } else {
                    choiceSelect.value = '';
                }
            }

            function syncMenuGroup() {
                const isSubcategory = parentSelect.value !== '';
                wrap.classList.toggle('d-none', !isSubcategory);
                choiceSelect.disabled = !isSubcategory;

                if (!isSubcategory) {
                    choiceSelect.value = '';
                    customWrap.classList.add('d-none');
                    customInput.value = '';
                    customInput.disabled = true;
                    return;
                }

                customInput.disabled = false;
                const isNew = choiceSelect.value === '__new__';
                customWrap.classList.toggle('d-none', !isNew);
                if (!isNew) {
                    customInput.value = '';
                }
            }

            parentSelect.addEventListener('change', function () {
                rebuildChoices('');
                syncMenuGroup();
            });
            choiceSelect.addEventListener('change', syncMenuGroup);

            rebuildChoices(initialChoice);
            syncMenuGroup();
        });
    </script>
@endpush
