@extends('layouts.agent')

@section('title', 'New Custom Request')
@section('page_title', 'New Custom Request')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('agent.custom-requests.index') }}">Custom Requests</a></li>
    <li class="breadcrumb-item active">New</li>
@endsection

@section('content')
@php
    use App\Support\Catalog;
    $colours = Catalog::colours();
    $finishes = Catalog::finishes();
    $upholsteries = Catalog::upholsteries();
@endphp

@if (! $primaryBranch)
    <div class="alert alert-warning">No primary branch is set. Ask head office to mark a branch as primary before sending requests.</div>
@else
    <p class="text-muted">Requests go to <strong>{{ $primaryBranch->name }}</strong>. Choose from the catalog lists only — you cannot add new classification options.</p>

    <form method="POST" action="{{ route('agent.custom-requests.store') }}" id="custom-request-form">
        @csrf

        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Classification</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="project_category" class="form-label">1 · Project category</label>
                        <select class="form-select @error('project_category') is-invalid @enderror" id="project_category" name="project_category">
                            <option value="">Select</option>
                            @foreach (Catalog::projects() as $projectOption)
                                <option value="{{ $projectOption }}" @selected($state['project'] === $projectOption)>{{ $projectOption }}</option>
                            @endforeach
                        </select>
                        @error('project_category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="room" class="form-label">2 · Room / space</label>
                        <select class="form-select @error('room') is-invalid @enderror" id="room" name="room" disabled>
                            <option value="">Select</option>
                        </select>
                        @error('room')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="product_category" class="form-label">3 · Product category</label>
                        <select class="form-select @error('product_category') is-invalid @enderror" id="product_category" name="product_category" disabled>
                            <option value="">Select</option>
                        </select>
                        @error('product_category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="design_type" class="form-label">4 · Type / design style</label>
                        <select class="form-select @error('design_type') is-invalid @enderror" id="design_type" name="design_type" disabled>
                            <option value="">Select</option>
                        </select>
                        @error('design_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="model_name" class="form-label">5 · Model</label>
                        <select class="form-select @error('model_name') is-invalid @enderror" id="model_name" name="model_name" disabled>
                            <option value="">Select</option>
                        </select>
                        @error('model_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="1" id="model_not_listed" name="model_not_listed" @checked($state['modelNotListed'])>
                            <label class="form-check-label" for="model_not_listed">Model not in catalog</label>
                        </div>
                    </div>
                </div>
                <p class="text-muted mb-0 mt-3 d-none" id="catalogue-trail"></p>
            </div>
        </div>

        <div class="card d-none" id="unmatched-card">
            <div class="card-header"><h5 class="card-title mb-0">Describe the product</h5></div>
            <div class="card-body">
                <p class="text-muted small">This model isn’t in the catalog, so colour and configuration options stay hidden. Name the product below and add customization notes.</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="requested_model_name" class="form-label required">Model name</label>
                        <input type="text" class="form-control @error('model_name') is-invalid @enderror" id="requested_model_name" maxlength="150" value="{{ old('model_name') }}">
                        @error('model_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Hidden so matched description can post as description; unmatched leaves it empty. --}}
        <textarea class="d-none" id="description" name="description" rows="1" tabindex="-1" aria-hidden="true">{{ old('description') }}</textarea>

        <div class="card d-none" id="config-card">
            <div class="card-header"><h5 class="card-title mb-0">Configuration</h5></div>
            <div class="card-body">
                <p class="text-muted small mb-3" id="family-note"></p>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="width_cm" class="form-label">Width (cm)</label>
                        <input type="number" class="form-control" id="width_cm" name="width_cm" value="{{ old('width_cm') }}" min="0" step="0.01">
                    </div>
                    <div class="col-md-3">
                        <label for="depth_cm" class="form-label">Depth (cm)</label>
                        <input type="number" class="form-control" id="depth_cm" name="depth_cm" value="{{ old('depth_cm') }}" min="0" step="0.01">
                    </div>
                    <div class="col-md-3">
                        <label for="height_cm" class="form-label">Height (cm)</label>
                        <input type="number" class="form-control" id="height_cm" name="height_cm" value="{{ old('height_cm') }}" min="0" step="0.01">
                    </div>
                    <div class="col-md-3">
                        <label for="weight_kg" class="form-label">Weight (kg)</label>
                        <input type="number" class="form-control" id="weight_kg" name="weight_kg" value="{{ old('weight_kg') }}" min="0" step="0.01">
                    </div>
                </div>
                <div class="row g-3 mt-0" id="family-fields"></div>
                <div class="row g-3 mt-0">
                    <div class="col-12">
                        <label for="matched_description" class="form-label">Description</label>
                        <textarea class="form-control" id="matched_description" rows="3">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card d-none" id="variants-card">
            <div class="card-header"><h5 class="card-title mb-0">Colours, finishes and variants</h5></div>
            <div class="card-body">
                <label class="form-label">Colours</label>
                <div class="d-flex flex-wrap gap-2 mb-3" id="colour-chips">
                    @foreach ($colours as $colourName => $hex)
                        <div>
                            <input type="checkbox" class="btn-check js-chip" data-group="colour" data-value="{{ $colourName }}" id="colour-{{ $loop->index }}" autocomplete="off">
                            <label class="btn btn-sm btn-outline-secondary" for="colour-{{ $loop->index }}">
                                <span class="d-inline-block rounded-circle border align-middle me-1" style="width:.75rem;height:.75rem;background:{{ $hex }};"></span>{{ $colourName }}
                            </label>
                        </div>
                    @endforeach
                </div>
                <label class="form-label">Finishes</label>
                <div class="d-flex flex-wrap gap-2 mb-3" id="finish-chips">
                    @foreach ($finishes as $finish)
                        <div>
                            <input type="checkbox" class="btn-check js-chip" data-group="finish" data-value="{{ $finish }}" id="finish-{{ $loop->index }}" autocomplete="off">
                            <label class="btn btn-sm btn-outline-secondary" for="finish-{{ $loop->index }}">{{ $finish }}</label>
                        </div>
                    @endforeach
                </div>
                <div class="mb-3 d-none" id="upholstery-group">
                    <label class="form-label">Upholstery</label>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($upholsteries as $upholstery)
                            <div>
                                <input type="checkbox" class="btn-check js-chip" data-group="upholstery" data-value="{{ $upholstery }}" id="upholstery-{{ $loop->index }}" autocomplete="off">
                                <label class="btn btn-sm btn-outline-secondary" for="upholstery-{{ $loop->index }}">{{ $upholstery }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <button type="button" class="btn btn-soft-primary mb-3" id="generate-variants">Generate variants</button>
                @error('variants')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr id="variant-head"></tr></thead>
                        <tbody id="variant-rows"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Customer &amp; customization notes</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="customer_name" class="form-label">Customer name</label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name" value="{{ old('customer_name') }}">
                    </div>
                    <div class="col-md-6">
                        <label for="customer_phone" class="form-label">Customer phone</label>
                        <input type="text" class="form-control" id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}">
                    </div>
                    <div class="col-12">
                        <label for="notes" class="form-label required">Customization notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="4" required>{{ old('notes') }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4 d-flex gap-2">
            <button type="submit" class="btn btn-success">Send Request</button>
            <a href="{{ route('agent.custom-requests.index') }}" class="btn btn-light">Cancel</a>
        </div>
    </form>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('custom-request-form');
    if (!form) return;

    const cfg = @json($catalog);
    const state = @json($state);
    const el = (id) => document.getElementById(id);

    const projectSelect = el('project_category');
    const roomSelect = el('room');
    const categorySelect = el('product_category');
    const typeSelect = el('design_type');
    const modelSelect = el('model_name');
    const notListed = el('model_not_listed');
    const unmatchedCard = el('unmatched-card');
    const configCard = el('config-card');
    const variantsCard = el('variants-card');
    const requestedModel = el('requested_model_name');
    const matchedDescription = el('matched_description');
    const description = el('description');
    const familyFields = el('family-fields');
    const familyNote = el('family-note');
    const upholsteryGroup = el('upholstery-group');
    const variantHead = el('variant-head');
    const variantRows = el('variant-rows');
    const trail = el('catalogue-trail');
    function option(value, label, selected) {
        const node = document.createElement('option');
        node.value = value;
        node.textContent = label;
        node.selected = !!selected;
        return node;
    }

    function fill(select, values, current, placeholder) {
        select.textContent = '';
        select.appendChild(option('', placeholder));
        values.forEach((value) => select.appendChild(option(value, value, value === current)));
    }

    function family() {
        return (cfg.families[state.project] || {})[state.category] || '';
    }

    function usesUpholstery() {
        return (cfg.upholsteryFamilies || []).indexOf(family()) !== -1;
    }

    function selected(group) {
        return Array.from(document.querySelectorAll('.js-chip[data-group="' + group + '"]:checked')).map((n) => n.dataset.value);
    }

    function renderFamilyFields() {
        const current = family();
        const config = (cfg.familyFields || {})[current];
        if (!config) {
            familyFields.innerHTML = '';
            familyNote.textContent = '';
            return;
        }
        familyNote.textContent = 'Fields below are specific to ' + config.label + '.';
        familyFields.innerHTML = config.fields.map(function (field, index) {
            const label = field.label;
            const id = 'spec-' + index;
            const name = 'specs[' + label + ']';
            const saved = state.specs[label];
            let control;
            if (field.type === 's') {
                const options = field.options || [];
                const value = saved !== undefined && options.indexOf(saved) !== -1 ? saved : '';
                control = '<select class="form-select" id="' + id + '" name="' + name + '">' +
                    '<option value="">Select</option>' +
                    options.map((choice) => '<option value="' + choice + '"' + (choice === value ? ' selected' : '') + '>' + choice + '</option>').join('') +
                    '</select>';
            } else {
                const value = saved === undefined || saved === null ? '' : saved;
                control = '<input type="number" class="form-control" id="' + id + '" name="' + name + '" value="' + value + '" step="0.01">';
            }
            return '<div class="col-md-3"><label class="form-label" for="' + id + '">' + label + '</label>' + control + '</div>';
        }).join('');
    }

    function renderVariants(rows) {
        state.variants = rows || [];
        const upholstery = usesUpholstery();
        const columns = ['Colour', 'Finish'];
        if (upholstery) columns.push('Upholstery');
        columns.push('');
        variantHead.innerHTML = columns.map((c) => '<th>' + c + '</th>').join('');
        variantRows.innerHTML = state.variants.map(function (row, index) {
            return '<tr>' +
                '<td><input type="hidden" name="variants[' + index + '][colour]" value="' + row.colour + '">' + row.colour + '</td>' +
                '<td><input type="hidden" name="variants[' + index + '][finish]" value="' + row.finish + '">' + row.finish + '</td>' +
                (upholstery ? '<td><input type="hidden" name="variants[' + index + '][upholstery]" value="' + (row.upholstery || '') + '">' + (row.upholstery || '—') + '</td>' : '<td class="d-none"><input type="hidden" name="variants[' + index + '][upholstery]" value=""></td>') +
                '<td><button type="button" class="btn btn-sm btn-soft-danger js-remove-variant" data-index="' + index + '">Remove</button></td>' +
                '</tr>';
        }).join('');
    }

    function generateVariants() {
        const colours = selected('colour');
        const finishes = selected('finish');
        const upholsteries = usesUpholstery() ? selected('upholstery') : [''];
        if (!colours.length || !finishes.length || (usesUpholstery() && !upholsteries.filter(Boolean).length)) {
            alert('Select at least one colour and finish' + (usesUpholstery() ? ', and upholstery' : '') + '.');
            return;
        }
        const rows = [];
        colours.forEach((colour) => finishes.forEach((finish) => upholsteries.forEach((upholstery) => {
            rows.push({ colour, finish, upholstery });
        })));
        renderVariants(rows);
    }

    function syncVisibility() {
        const unmatched = notListed.checked;
        const ready = !unmatched && state.project && state.room && state.category && state.type && state.model;

        unmatchedCard.classList.toggle('d-none', !unmatched);
        configCard.classList.toggle('d-none', !ready);
        variantsCard.classList.toggle('d-none', !ready);
        modelSelect.disabled = unmatched || !state.category;
        modelSelect.required = !unmatched;
        requestedModel.required = unmatched;

        if (unmatched) {
            modelSelect.value = '';
            state.model = '';
            if (requestedModel.value) {
                // keep free-text model in a hidden-compatible way via model_name on submit
            }
        }

        trail.classList.toggle('d-none', !(state.project && state.room && state.category));
        trail.textContent = [state.project, state.room, state.category, state.type, state.model].filter(Boolean).join(' › ');

        if (ready) {
            renderFamilyFields();
            upholsteryGroup.classList.toggle('d-none', !usesUpholstery());
            if (!state.variants.length) {
                renderVariants([]);
            }
        }
    }

    function refreshCascade(level) {
        if (level <= 1) {
            state.project = projectSelect.value;
            const rooms = Object.keys((cfg.tree[state.project] || {}));
            fill(roomSelect, rooms, state.room, 'Select');
            roomSelect.disabled = !state.project;
            if (level < 1) state.room = '';
        }
        if (level <= 2) {
            if (level < 2) state.room = roomSelect.value;
            else state.room = roomSelect.value;
            const categories = (cfg.tree[state.project] || {})[state.room] || [];
            fill(categorySelect, categories, state.category, 'Select');
            categorySelect.disabled = !state.room;
            if (level < 2) state.category = '';
        }
        if (level <= 3) {
            state.category = categorySelect.value;
            fill(typeSelect, cfg.types[state.category] || [], state.type, 'Select');
            typeSelect.disabled = !state.category;
            fill(modelSelect, cfg.models[state.category] || [], state.model, 'Select');
            modelSelect.disabled = notListed.checked || !state.category;
            if (level < 3) {
                state.type = '';
                state.model = '';
            }
        }
        if (level <= 4) {
            state.type = typeSelect.value;
            if (level < 4) state.model = '';
        }
        if (level <= 5) {
            state.model = modelSelect.value;
        }
        syncVisibility();
    }

    projectSelect.addEventListener('change', () => refreshCascade(1));
    roomSelect.addEventListener('change', () => refreshCascade(2));
    categorySelect.addEventListener('change', () => refreshCascade(3));
    typeSelect.addEventListener('change', () => refreshCascade(4));
    modelSelect.addEventListener('change', () => refreshCascade(5));
    notListed.addEventListener('change', syncVisibility);
    el('generate-variants').addEventListener('click', generateVariants);
    variantRows.addEventListener('click', function (event) {
        const btn = event.target.closest('.js-remove-variant');
        if (!btn) return;
        state.variants.splice(Number(btn.dataset.index), 1);
        renderVariants(state.variants);
    });

    form.addEventListener('submit', function () {
        if (notListed.checked) {
            // Ensure model_name carries the free-text name.
            let hidden = form.querySelector('input[name="model_name"][data-free-text]');
            if (!hidden) {
                hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'model_name';
                hidden.dataset.freeText = '1';
                form.appendChild(hidden);
            }
            hidden.value = requestedModel.value;
            modelSelect.disabled = true;
            description.value = '';
            matchedDescription.disabled = true;
        } else {
            matchedDescription.name = 'description';
            description.disabled = true;
            requestedModel.disabled = true;
        }
    });

    // Restore old variants if validation failed.
    if (Array.isArray(state.variants) && state.variants.length) {
        renderVariants(state.variants.map((row) => ({
            colour: row.colour || '',
            finish: row.finish || '',
            upholstery: row.upholstery || '',
        })));
    }

    refreshCascade(1);
    if (state.room) refreshCascade(2);
    if (state.category) refreshCascade(3);
    if (state.type) refreshCascade(4);
    if (state.model) refreshCascade(5);
    if (state.modelNotListed) {
        notListed.checked = true;
        requestedModel.value = state.model || requestedModel.value;
        syncVisibility();
    }
});
</script>
@endpush
