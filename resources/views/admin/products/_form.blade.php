@php
    use App\Models\ProductVariant;
    use App\Support\Catalog;

    $isEdit = $product->exists;
    $colours = Catalog::colours();
    $finishes = Catalog::finishes();
    $upholsteries = Catalog::upholsteries();
    $oldVariants = old('variants');

    if (is_array($oldVariants)) {
        $variantRows = collect($oldVariants)
            ->filter(fn ($row) => is_array($row))
            ->map(fn ($row) => [
                'colour' => (string) ($row['colour'] ?? ''),
                'finish' => (string) ($row['finish'] ?? ''),
                'upholstery' => (string) ($row['upholstery'] ?? ''),
                'price' => (string) ($row['price'] ?? ''),
                'compare_at_price' => (string) ($row['compare_at_price'] ?? ''),
                'status' => (string) ($row['status'] ?? ProductVariant::STATUS_ACTIVE),
                'image_urls' => (string) ($row['image_urls'] ?? ''),
                'stock' => null,
            ])
            ->values()
            ->all();
    } else {
        $variantRows = $product->variants
            ->map(fn (ProductVariant $variant) => [
                'colour' => (string) $variant->colour,
                'finish' => (string) $variant->finish,
                'upholstery' => (string) $variant->upholstery,
                'price' => (string) $variant->price,
                'compare_at_price' => $variant->compare_at_price === null ? '' : (string) $variant->compare_at_price,
                'status' => (string) $variant->status,
                'image_urls' => $variant->relationLoaded('images')
                    ? $variant->images->pluck('url')->implode("\n")
                    : '',
                'stock' => $variant->relationLoaded('stocks') ? (int) $variant->stocks->sum('quantity') : null,
            ])
            ->values()
            ->all();
    }

    $state = [
        'project' => (string) old('project_category', $product->project_category),
        'room' => (string) old('room', $product->room),
        'category' => (string) old('product_category', $product->product_category),
        'type' => (string) old('design_type', $product->design_type),
        'model' => (string) old('name', $product->name),
        'code' => (string) old('code', $product->code),
        'specs' => (object) old('specs', $product->specs ?? []),
        'variants' => $variantRows,
        'isEdit' => $isEdit,
        'sequence' => $nextSequence,
    ];

    $ready = $state['category'] !== '' && $state['type'] !== '' && $state['model'] !== '';
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.products.update', $product) : route('admin.products.store') }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="card">
        <div class="card-header"><h5 class="card-title mb-0">Classification</h5></div>
        <div class="card-body">
            <p class="text-muted small">Each choice fills the next level. Changing a level clears the ones below it.</p>
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="business_group" class="form-label">1 · Business group</label>
                    <select class="form-select" id="business_group" disabled>
                        <option>{{ config('catalog.business_group') }}</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="project_category" class="form-label required">2 · Project category</label>
                    <select class="form-select @error('project_category') is-invalid @enderror" id="project_category" name="project_category" required>
                        <option value="">Select</option>
                        @foreach (Catalog::projects() as $projectOption)
                            <option value="{{ $projectOption }}" @selected($state['project'] === $projectOption)>{{ $projectOption }}</option>
                        @endforeach
                    </select>
                    @error('project_category')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="room" class="form-label required">3 · Room / space</label>
                    <select class="form-select @error('room') is-invalid @enderror" id="room" name="room" required disabled>
                        <option value="">Select</option>
                    </select>
                    @error('room')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="product_category" class="form-label required">4 · Product category</label>
                    <select class="form-select @error('product_category') is-invalid @enderror" id="product_category" name="product_category" required disabled>
                        <option value="">Select</option>
                    </select>
                    @error('product_category')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="design_type" class="form-label required">5 · Type / design style</label>
                    <div class="input-group">
                        <select class="form-select @error('design_type') is-invalid @enderror" id="design_type" name="design_type" required disabled>
                            <option value="">Select</option>
                        </select>
                        <button type="button" class="btn btn-outline-secondary" id="new-type-toggle" disabled>Add new</button>
                    </div>
                    @error('design_type')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    <div class="input-group mt-2 d-none" id="new-type-wrap">
                        <input type="text" class="form-control" id="new-type-input" placeholder="New type name" maxlength="100">
                        <button type="button" class="btn btn-outline-secondary" id="new-type-save">Add</button>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="product_model" class="form-label required">6 · Product model</label>
                    <div class="input-group">
                        <select class="form-select @error('name') is-invalid @enderror" id="product_model" name="name" required disabled>
                            <option value="">Select</option>
                        </select>
                        <button type="button" class="btn btn-outline-secondary" id="new-model-toggle" disabled>Create new</button>
                    </div>
                    <div class="form-text">The model becomes the product name.</div>
                    @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    <div class="input-group mt-2 d-none" id="new-model-wrap">
                        <input type="text" class="form-control" id="new-model-input" placeholder="New model name" maxlength="150">
                        <button type="button" class="btn btn-outline-secondary" id="new-model-save">Add</button>
                    </div>
                </div>
            </div>
            <p class="text-muted mb-0 mt-3 {{ $ready ? '' : 'd-none' }}" id="catalogue-trail"></p>
        </div>
    </div>

    <div class="card {{ $ready ? '' : 'd-none' }}" id="config-card">
        <div class="card-header"><h5 class="card-title mb-0">Configuration</h5></div>
        <div class="card-body">
            <p class="text-muted small mb-3" id="family-note"></p>
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="width_cm" class="form-label">Width (cm)</label>
                    <input type="number" class="form-control @error('width_cm') is-invalid @enderror" id="width_cm" name="width_cm" value="{{ old('width_cm', $product->width_cm) }}" min="0" step="0.01">
                    @error('width_cm')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="depth_cm" class="form-label">Depth (cm)</label>
                    <input type="number" class="form-control @error('depth_cm') is-invalid @enderror" id="depth_cm" name="depth_cm" value="{{ old('depth_cm', $product->depth_cm) }}" min="0" step="0.01">
                    @error('depth_cm')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="height_cm" class="form-label">Height (cm)</label>
                    <input type="number" class="form-control @error('height_cm') is-invalid @enderror" id="height_cm" name="height_cm" value="{{ old('height_cm', $product->height_cm) }}" min="0" step="0.01">
                    @error('height_cm')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="weight_kg" class="form-label">Weight (kg)</label>
                    <input type="number" class="form-control @error('weight_kg') is-invalid @enderror" id="weight_kg" name="weight_kg" value="{{ old('weight_kg', $product->weight_kg) }}" min="0" step="0.01">
                    @error('weight_kg')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row g-3 mt-0" id="family-fields"></div>
            <div class="row g-3 mt-0">
                <div class="col-md-3">
                    <label for="base_price" class="form-label required">Base price (₹)</label>
                    <input type="number" class="form-control @error('base_price') is-invalid @enderror" id="base_price" name="base_price" value="{{ old('base_price', $product->exists ? $product->base_price : '') }}" min="0" step="0.01" required>
                    <div class="form-text">Starting price for new variants.</div>
                    @error('base_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $product->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card {{ $ready ? '' : 'd-none' }}" id="variants-card">
        <div class="card-header"><h5 class="card-title mb-0">Colours, finishes and variants</h5></div>
        <div class="card-body">
            <label class="form-label">Colours</label>
            <div class="d-flex flex-wrap gap-2 mb-3" id="colour-chips">
                @foreach ($colours as $colourName => $hex)
                    <div>
                        <input type="checkbox" class="btn-check js-chip" data-group="colour" data-value="{{ $colourName }}" id="colour-{{ $loop->index }}" autocomplete="off">
                        <label class="btn btn-sm btn-outline-secondary" for="colour-{{ $loop->index }}">
                            <span class="d-inline-block rounded-circle border align-middle me-1" style="width: .75rem; height: .75rem; background: {{ $hex }};"></span>{{ $colourName }}
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

            <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                <button type="button" class="btn btn-soft-primary" id="generate-variants">Generate variants</button>
                <span class="text-muted small" id="variant-note"></span>
            </div>
            <div class="text-danger small mb-2 d-none" id="variant-warning"></div>
            @error('variants')
                <div class="text-danger small mb-2">{{ $message }}</div>
            @enderror

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr id="variant-head"></tr>
                    </thead>
                    <tbody id="variant-rows"></tbody>
                </table>
            </div>
            <p class="text-muted small mb-0 mt-2">Prices are per variant. A removed variant that has stock or past orders is kept as inactive.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="card-title mb-0">Product details</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="code" class="form-label {{ $isEdit ? '' : 'required' }}">Product code</label>
                    @if ($isEdit)
                        <input type="text" class="form-control" id="code" value="{{ $product->code }}" readonly>
                        <div class="form-text">The code cannot change after saving.</div>
                    @else
                        <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" placeholder="GI-F-LI-SOF-0001" maxlength="30" data-sequence="{{ $nextSequence }}" required>
                        <div class="form-text">Suggested from the classification. Edit it if you keep your own codes.</div>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    @endif
                </div>
                <div class="col-md-4">
                    <label for="slug" class="form-label">Slug</label>
                    <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $product->slug) }}" maxlength="160">
                    <div class="form-text">Used in the store product URL. Left blank it follows the model.</div>
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="unit" class="form-label required">Unit</label>
                    <input type="text" class="form-control @error('unit') is-invalid @enderror" id="unit" name="unit" value="{{ old('unit', $product->unit) }}" maxlength="30" required>
                    @error('unit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="material" class="form-label">Material</label>
                    <input type="text" class="form-control @error('material') is-invalid @enderror" id="material" name="material" value="{{ old('material', $product->material) }}" placeholder="Sheesham wood and velvet" maxlength="100">
                    @error('material')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <div class="form-check mt-4">
                        <input type="checkbox" class="form-check-input @error('is_online') is-invalid @enderror" id="is_online" name="is_online" value="1" @checked(old('is_online', $product->is_online))>
                        <label class="form-check-label" for="is_online">Show on store</label>
                        @error('is_online')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check mt-4">
                        <input type="checkbox" class="form-check-input @error('is_featured') is-invalid @enderror" id="is_featured" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))>
                        <label class="form-check-label" for="is_featured">Featured on home</label>
                        @error('is_featured')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-12">
                    <label for="image_urls" class="form-label">Shared image urls</label>
                    <textarea class="form-control @error('image_urls') is-invalid @enderror" id="image_urls" name="image_urls" rows="3" placeholder="One image URL per line — used when a variant has no photos of its own">{{ old('image_urls', $imageUrls) }}</textarea>
                    <div class="form-text">Optional fallback gallery. Prefer photos on each variant below.</div>
                    @error('image_urls')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                @include('admin.partials.record-status', ['statusId' => 'product_record_status', 'record' => $product])
            </div>
        </div>
    </div>

    @if ($isEdit && $product->variants->isNotEmpty())
        @if ($branches->isNotEmpty())
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Showroom prices</h5></div>
                <div class="card-body">
                    <p class="text-muted small">Price per variant at each showroom. Leave a cell blank to use the variant price.</p>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Variant</th>
                                    @foreach ($branches as $branch)
                                        <th>{{ $branch->name }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($product->variants as $variant)
                                    <tr>
                                        <td class="text-nowrap">
                                            <span class="fw-medium">{{ $variant->sku }}</span>
                                            <div class="text-muted small">{{ $variant->label() }}</div>
                                        </td>
                                        @foreach ($branches as $branch)
                                            <td style="min-width: 9rem;">
                                                <input type="number" class="form-control @error('branch_prices.'.$variant->id.'.'.$branch->id) is-invalid @enderror" name="branch_prices[{{ $variant->id }}][{{ $branch->id }}]" value="{{ old('branch_prices.'.$variant->id.'.'.$branch->id, $branchPrices[$variant->id][$branch->id] ?? null) }}" min="0" step="0.01">
                                                @error('branch_prices.'.$variant->id.'.'.$branch->id)
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if ($agents->isNotEmpty())
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Shop prices</h5></div>
                <div class="card-body">
                    <p class="text-muted small">Price and commission per variant at each shop. Leave the price blank to use the variant price, and the commission blank to use the shop’s usual rate shown beside its name.</p>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Variant</th>
                                    @foreach ($agents as $agent)
                                        <th>
                                            {{ $agent->name }}
                                            @if ($agent->commission_percent !== null)
                                                <span class="text-muted">({{ rtrim(rtrim(number_format((float) $agent->commission_percent, 2), '0'), '.') }}%)</span>
                                            @endif
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($product->variants as $variant)
                                    <tr>
                                        <td class="text-nowrap">
                                            <span class="fw-medium">{{ $variant->sku }}</span>
                                            <div class="text-muted small">{{ $variant->label() }}</div>
                                        </td>
                                        @foreach ($agents as $agent)
                                            @php
                                                $term = $agentTerms[$variant->id][$agent->id] ?? null;
                                            @endphp
                                            <td style="min-width: 11rem;">
                                                <input type="number" class="form-control @error('agent_terms.'.$variant->id.'.'.$agent->id.'.price') is-invalid @enderror" name="agent_terms[{{ $variant->id }}][{{ $agent->id }}][price]" value="{{ old('agent_terms.'.$variant->id.'.'.$agent->id.'.price', $term?->price) }}" min="0" step="0.01" placeholder="Price">
                                                @error('agent_terms.'.$variant->id.'.'.$agent->id.'.price')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <input type="number" class="form-control mt-1 @error('agent_terms.'.$variant->id.'.'.$agent->id.'.commission_percent') is-invalid @enderror" name="agent_terms[{{ $variant->id }}][{{ $agent->id }}][commission_percent]" value="{{ old('agent_terms.'.$variant->id.'.'.$agent->id.'.commission_percent', $term?->commission_percent) }}" min="0" max="100" step="0.01" placeholder="Commission %">
                                                @error('agent_terms.'.$variant->id.'.'.$agent->id.'.commission_percent')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    @elseif ($branches->isNotEmpty() || $agents->isNotEmpty())
        <div class="card">
            <div class="card-body">
                <h6 class="mb-1">Showroom and shop prices</h6>
                <p class="text-muted small mb-0">Save the product first. Showroom and shop prices are set per variant, so they open for editing once the variants exist.</p>
            </div>
        </div>
    @endif

    <div class="mb-4 d-flex gap-2">
        <button type="submit" class="btn btn-success">{{ $isEdit ? 'Save changes' : 'Save product' }}</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const cfg = @json($catalog);
            const state = @json($state);
            const errors = @json($errors->getMessages());
            const el = function (id) { return document.getElementById(id); };

            const projectSelect = el('project_category');
            const roomSelect = el('room');
            const categorySelect = el('product_category');
            const typeSelect = el('design_type');
            const modelSelect = el('product_model');
            const newTypeToggle = el('new-type-toggle');
            const newTypeWrap = el('new-type-wrap');
            const newTypeInput = el('new-type-input');
            const newModelToggle = el('new-model-toggle');
            const newModelWrap = el('new-model-wrap');
            const newModelInput = el('new-model-input');
            const trail = el('catalogue-trail');
            const configCard = el('config-card');
            const variantsCard = el('variants-card');
            const familyFields = el('family-fields');
            const familyNote = el('family-note');
            const upholsteryGroup = el('upholstery-group');
            const variantHead = el('variant-head');
            const variantRows = el('variant-rows');
            const variantNote = el('variant-note');
            const variantWarning = el('variant-warning');
            const basePrice = el('base_price');
            const description = el('description');
            const codeInput = el('code');

            const extraTypes = [];
            const extraModels = [];
            let codeTouched = state.isEdit || (codeInput.value !== '');
            let descriptionTouched = description.value !== '';

            function esc(value) {
                return String(value === null || value === undefined ? '' : value).replace(/[&<>"']/g, function (match) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[match];
                });
            }

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
                values.forEach(function (value) {
                    select.appendChild(option(value, value, value === current));
                });
            }

            function family() {
                const byProject = cfg.families[state.project] || {};
                return byProject[state.category] || '';
            }

            function usesUpholstery() {
                return cfg.upholsteryFamilies.indexOf(family()) !== -1;
            }

            function colourHex(name) {
                return cfg.colours[name] || '#888888';
            }

            function skuFor(colour, finish, upholstery) {
                const letters = String(colour).replace(/[^A-Za-z]/g, '').slice(0, 3);
                const suffix = letters + String(finish).slice(0, 1) + (upholstery ? String(upholstery).slice(0, 2) : '');
                return (String(state.code || 'CODE') + '-' + suffix).toUpperCase();
            }

            function suggestedCode() {
                if (!state.project || !state.room || !state.category) {
                    return '';
                }
                const prefix = state.project === 'Interiors' ? 'I' : 'F';
                const room = state.room.slice(0, 2).toUpperCase();
                const category = state.category.replace(/[^A-Za-z]/g, '').slice(0, 3).toUpperCase();
                return 'GI-' + prefix + '-' + room + '-' + category + '-' + String(state.sequence).padStart(4, '0');
            }

            function errorFor(key) {
                return errors[key] ? errors[key][0] : '';
            }

            function invalidFeedback(key) {
                const message = errorFor(key);
                return message === '' ? '' : '<div class="invalid-feedback">' + esc(message) + '</div>';
            }

            function captureSpecs() {
                familyFields.querySelectorAll('[data-spec-label]').forEach(function (field) {
                    state.specs[field.dataset.specLabel] = field.value;
                });
            }

            function renderFamilyFields() {
                const current = family();
                const config = cfg.familyFields[current];

                if (!config) {
                    familyFields.innerHTML = '';
                    familyNote.textContent = '';
                    return;
                }

                familyNote.textContent = 'Fields below are specific to ' + config.label + '.';
                familyFields.innerHTML = config.fields.map(function (field, index) {
                    const label = field.label;
                    const key = 'specs.' + label;
                    const name = 'specs[' + label + ']';
                    const id = 'spec-' + index;
                    const saved = state.specs[label];
                    const invalid = errorFor(key) === '' ? '' : ' is-invalid';
                    let control;

                    if (field.type === 's') {
                        const options = field.options || [];
                        const value = saved !== undefined && options.indexOf(saved) !== -1 ? saved : '';
                        control = '<select class="form-select' + invalid + '" id="' + id + '" name="' + esc(name) + '" data-spec-label="' + esc(label) + '">'
                            + '<option value="">Select</option>'
                            + options.map(function (choice) {
                                return '<option value="' + esc(choice) + '"' + (choice === value ? ' selected' : '') + '>' + esc(choice) + '</option>';
                            }).join('')
                            + '</select>';
                    } else {
                        const value = saved === undefined || saved === null ? '' : saved;
                        control = '<input type="number" class="form-control' + invalid + '" id="' + id + '" name="' + esc(name) + '" value="' + esc(value) + '" step="0.01" data-spec-label="' + esc(label) + '">';
                    }

                    return '<div class="col-md-3">'
                        + '<label for="' + id + '" class="form-label">' + esc(label) + '</label>'
                        + control
                        + invalidFeedback(key)
                        + '</div>';
                }).join('');

                captureSpecs();
            }

            function renderVariants() {
                const upholstery = usesUpholstery();
                const columns = ['SKU', 'Colour', 'Finish'];

                if (upholstery) {
                    columns.push('Upholstery');
                }

                columns.push('Price (₹)', 'MRP (₹)', 'Status', 'Images');

                if (state.isEdit) {
                    columns.push('Stock');
                }

                columns.push('');
                variantHead.innerHTML = columns.map(function (column) {
                    return '<th>' + esc(column) + '</th>';
                }).join('');

                variantRows.innerHTML = state.variants.map(function (row, index) {
                    const name = 'variants[' + index + ']';
                    const value = upholstery ? row.upholstery : '';
                    const priceKey = 'variants.' + index + '.price';
                    const mrpKey = 'variants.' + index + '.compare_at_price';
                    const imagesKey = 'variants.' + index + '.image_urls';

                    return '<tr>'
                        + '<td class="text-nowrap small">' + esc(skuFor(row.colour, row.finish, value))
                        + '<input type="hidden" name="' + name + '[colour]" value="' + esc(row.colour) + '">'
                        + '<input type="hidden" name="' + name + '[finish]" value="' + esc(row.finish) + '">'
                        + '<input type="hidden" name="' + name + '[upholstery]" value="' + esc(value) + '">'
                        + '</td>'
                        + '<td class="text-nowrap"><span class="d-inline-block rounded-circle border align-middle me-1" style="width: .75rem; height: .75rem; background: ' + esc(colourHex(row.colour)) + ';"></span>' + esc(row.colour) + '</td>'
                        + '<td class="text-nowrap">' + esc(row.finish) + '</td>'
                        + (upholstery ? '<td class="text-nowrap">' + esc(value || '—') + '</td>' : '')
                        + '<td><input type="number" class="form-control js-variant-field' + (errorFor(priceKey) ? ' is-invalid' : '') + '" name="' + name + '[price]" value="' + esc(row.price) + '" min="0" step="0.01" required style="min-width: 7rem;" data-index="' + index + '" data-field="price">' + invalidFeedback(priceKey) + '</td>'
                        + '<td><input type="number" class="form-control js-variant-field' + (errorFor(mrpKey) ? ' is-invalid' : '') + '" name="' + name + '[compare_at_price]" value="' + esc(row.compare_at_price) + '" min="0" step="0.01" style="min-width: 7rem;" data-index="' + index + '" data-field="compare_at_price">' + invalidFeedback(mrpKey) + '</td>'
                        + '<td><select class="form-select js-variant-field" name="' + name + '[status]" data-index="' + index + '" data-field="status" style="min-width: 7rem;">'
                        + '<option value="active"' + (row.status === 'inactive' ? '' : ' selected') + '>Active</option>'
                        + '<option value="inactive"' + (row.status === 'inactive' ? ' selected' : '') + '>Inactive</option>'
                        + '</select></td>'
                        + '<td><textarea class="form-control js-variant-field' + (errorFor(imagesKey) ? ' is-invalid' : '') + '" name="' + name + '[image_urls]" rows="2" placeholder="One URL per line" style="min-width: 12rem;" data-index="' + index + '" data-field="image_urls">' + esc(row.image_urls || '') + '</textarea>' + invalidFeedback(imagesKey) + '</td>'
                        + (state.isEdit ? '<td class="text-nowrap">' + (row.stock === null ? '—' : esc(row.stock)) + '</td>' : '')
                        + '<td><button type="button" class="btn btn-sm btn-soft-danger js-remove-variant" data-index="' + index + '">Remove</button></td>'
                        + '</tr>';
                }).join('');

                variantNote.textContent = state.variants.length === 0
                    ? 'No variants yet. Pick colours and finishes, then generate variants.'
                    : state.variants.length + ' saved on submit. Edit price, MRP, and images per SKU.';
            }

            function picked(group) {
                return Array.from(document.querySelectorAll('.js-chip[data-group="' + group + '"]:checked')).map(function (chip) {
                    return chip.dataset.value;
                });
            }

            function checkChipsFromVariants() {
                const chosen = { colour: [], finish: [], upholstery: [] };

                state.variants.forEach(function (row) {
                    chosen.colour.push(row.colour);
                    chosen.finish.push(row.finish);
                    chosen.upholstery.push(row.upholstery);
                });

                document.querySelectorAll('.js-chip').forEach(function (chip) {
                    chip.checked = chosen[chip.dataset.group].indexOf(chip.dataset.value) !== -1;
                });
            }

            function generate() {
                const colours = picked('colour');
                const finishes = picked('finish');
                const upholsteries = usesUpholstery() ? picked('upholstery') : [''];

                if (colours.length === 0 || finishes.length === 0 || upholsteries.length === 0) {
                    variantWarning.textContent = 'Select at least one option in each group.';
                    variantWarning.classList.remove('d-none');
                    return;
                }

                variantWarning.classList.add('d-none');

                const existing = {};
                state.variants.forEach(function (row) {
                    existing[row.colour + '|' + row.finish + '|' + row.upholstery] = row;
                });

                const price = basePrice.value;
                const rows = [];

                colours.forEach(function (colour) {
                    finishes.forEach(function (finish) {
                        upholsteries.forEach(function (upholstery) {
                            const kept = existing[colour + '|' + finish + '|' + upholstery];
                            rows.push(kept || {
                                colour: colour,
                                finish: finish,
                                upholstery: upholstery,
                                price: price,
                                compare_at_price: '',
                                status: 'active',
                                image_urls: '',
                                stock: null,
                            });
                        });
                    });
                });

                state.variants = rows;
                renderVariants();
            }

            function sync() {
                const rooms = state.project && cfg.tree[state.project] ? Object.keys(cfg.tree[state.project]) : [];
                const categories = state.room && cfg.tree[state.project] ? (cfg.tree[state.project][state.room] || []) : [];
                const types = state.category ? (cfg.types[state.category] || []).concat(extraTypes) : [];
                const models = state.category ? (cfg.models[state.category] || []).concat(extraModels) : [];

                if (state.type && types.indexOf(state.type) === -1) {
                    types.push(state.type);
                }

                if (state.model && models.indexOf(state.model) === -1) {
                    models.push(state.model);
                }

                fill(roomSelect, rooms, state.room, 'Select');
                fill(categorySelect, categories, state.category, 'Select');
                fill(typeSelect, types, state.type, 'Select');
                fill(modelSelect, models, state.model, 'Select');

                roomSelect.disabled = !state.project;
                categorySelect.disabled = !state.room;
                typeSelect.disabled = !state.category;
                modelSelect.disabled = !state.type;
                newTypeToggle.disabled = !state.category;
                newModelToggle.disabled = !state.type;

                const ready = !!(state.category && state.type && state.model);
                trail.classList.toggle('d-none', !ready);
                configCard.classList.toggle('d-none', !ready);
                variantsCard.classList.toggle('d-none', !ready);
                upholsteryGroup.classList.toggle('d-none', !usesUpholstery());

                if (ready) {
                    trail.innerHTML = esc(cfg.businessGroup) + ' › <span class="text-body">' + [state.project, state.room, state.category, state.type, state.model].map(esc).join('</span> › <span class="text-body">') + '</span>';

                    if (!descriptionTouched) {
                        description.value = state.model + ' – ' + state.type.toLowerCase() + ' ' + state.category.toLowerCase() + ', crafted by Godwin Industries.';
                    }
                }

                if (!state.isEdit && !codeTouched) {
                    codeInput.value = suggestedCode();
                }

                renderFamilyFields();
                renderVariants();
            }

            function setLevel(level, value) {
                if (level === 'project') {
                    state.project = value;
                    state.room = state.category = state.type = state.model = '';
                } else if (level === 'room') {
                    state.room = value;
                    state.category = state.type = state.model = '';
                } else if (level === 'category') {
                    state.category = value;
                    state.type = state.model = '';
                } else if (level === 'type') {
                    state.type = value;
                    state.model = '';
                } else {
                    state.model = value;
                }

                if (level !== 'model' && level !== 'type') {
                    state.variants = [];
                }

                captureSpecs();
                sync();
            }

            projectSelect.addEventListener('change', function () { setLevel('project', this.value); });
            roomSelect.addEventListener('change', function () { setLevel('room', this.value); });
            categorySelect.addEventListener('change', function () { setLevel('category', this.value); });
            typeSelect.addEventListener('change', function () { setLevel('type', this.value); });
            modelSelect.addEventListener('change', function () { setLevel('model', this.value); });

            newTypeToggle.addEventListener('click', function () {
                newTypeWrap.classList.toggle('d-none');
                newTypeInput.focus();
            });

            el('new-type-save').addEventListener('click', function () {
                const value = newTypeInput.value.trim();

                if (value === '') {
                    return;
                }

                if (extraTypes.indexOf(value) === -1) {
                    extraTypes.push(value);
                }

                newTypeInput.value = '';
                newTypeWrap.classList.add('d-none');
                setLevel('type', value);
            });

            newModelToggle.addEventListener('click', function () {
                newModelWrap.classList.toggle('d-none');
                newModelInput.focus();
            });

            el('new-model-save').addEventListener('click', function () {
                const value = newModelInput.value.trim();

                if (value === '') {
                    return;
                }

                if (extraModels.indexOf(value) === -1) {
                    extraModels.push(value);
                }

                newModelInput.value = '';
                newModelWrap.classList.add('d-none');
                setLevel('model', value);
            });

            el('generate-variants').addEventListener('click', generate);

            variantRows.addEventListener('input', function (event) {
                const field = event.target.closest('.js-variant-field');

                if (!field) {
                    return;
                }

                state.variants[Number(field.dataset.index)][field.dataset.field] = field.value;
            });

            variantRows.addEventListener('change', function (event) {
                const field = event.target.closest('.js-variant-field');

                if (field) {
                    state.variants[Number(field.dataset.index)][field.dataset.field] = field.value;
                }
            });

            variantRows.addEventListener('click', function (event) {
                const button = event.target.closest('.js-remove-variant');

                if (!button) {
                    return;
                }

                state.variants.splice(Number(button.dataset.index), 1);
                renderVariants();
                checkChipsFromVariants();
            });

            familyFields.addEventListener('change', captureSpecs);
            familyFields.addEventListener('input', captureSpecs);
            description.addEventListener('input', function () { descriptionTouched = true; });

            if (!state.isEdit) {
                codeInput.addEventListener('input', function () { codeTouched = true; });
            }

            codeInput.addEventListener('change', function () {
                state.code = codeInput.value;
                renderVariants();
            });

            checkChipsFromVariants();
            sync();
        });
    </script>
@endpush
