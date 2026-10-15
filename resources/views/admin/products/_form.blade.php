@php
    $isEdit = $product->exists;
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.products.update', $product) : route('admin.products.store') }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-md-4">
            <label for="code" class="form-label">Product Code</label>
            @if ($isEdit)
                <input type="text" class="form-control" id="code" value="{{ $product->code }}" readonly>
            @else
                <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" placeholder="SF001" required>
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            @endif
        </div>
        <div class="col-md-8">
            <label for="name" class="form-label">Product Name</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="slug" class="form-label">Slug</label>
            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $product->slug) }}">
            <div class="form-text">Used in the store product URL.</div>
            @error('slug')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="category_id" class="form-label">Subcategory</label>
            <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                <option value="">Select Subcategory</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>{{ $category->parent?->name }} · {{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="unit" class="form-label">Unit</label>
            <input type="text" class="form-control @error('unit') is-invalid @enderror" id="unit" name="unit" value="{{ old('unit', $product->unit) }}" required>
            @error('unit')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="material" class="form-label">Material</label>
            <input type="text" class="form-control @error('material') is-invalid @enderror" id="material" name="material" value="{{ old('material', $product->material) }}" placeholder="CRCA Steel & Velvet">
            @error('material')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="selling_price" class="form-label">Selling Price</label>
            <input type="number" class="form-control @error('selling_price') is-invalid @enderror" id="selling_price" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" min="0" step="0.01" required>
            <div class="form-text">Base counter price when a branch has no override.</div>
            @error('selling_price')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="online_price" class="form-label">Online Price</label>
            <input type="number" class="form-control @error('online_price') is-invalid @enderror" id="online_price" name="online_price" value="{{ old('online_price', $product->online_price) }}" min="0" step="0.01">
            <div class="form-text">Website price. Leave blank to use Selling Price.</div>
            @error('online_price')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="compare_at_price" class="form-label">MRP</label>
            <input type="number" class="form-control @error('compare_at_price') is-invalid @enderror" id="compare_at_price" name="compare_at_price" value="{{ old('compare_at_price', $product->compare_at_price) }}" min="0" step="0.01">
            <div class="form-text">Struck price on the store. Not charged.</div>
            @error('compare_at_price')
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
        <div class="col-12">
            <label for="image_urls" class="form-label">Image Urls</label>
            <textarea class="form-control @error('image_urls') is-invalid @enderror" id="image_urls" name="image_urls" rows="4" placeholder="One image URL per line">{{ old('image_urls', $imageUrls) }}</textarea>
            @error('image_urls')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <div class="form-check mt-4">
                <input type="checkbox" class="form-check-input @error('is_online') is-invalid @enderror" id="is_online" name="is_online" value="1" @checked(old('is_online', $product->is_online))>
                <label class="form-check-label" for="is_online">Show On Store</label>
                @error('is_online')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-check mt-4">
                <input type="checkbox" class="form-check-input @error('is_featured') is-invalid @enderror" id="is_featured" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))>
                <label class="form-check-label" for="is_featured">Featured On Home</label>
                @error('is_featured')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        @include('admin.partials.record-status', ['statusId' => 'product_record_status', 'record' => $product])
    </div>

    @if ($branches->isNotEmpty())
        <div class="mt-4">
            <h6 class="mb-3">Branch Prices</h6>
            <p class="text-muted small">Leave blank to use Selling Price at that showroom.</p>
            <div class="row g-3">
                @foreach ($branches as $branch)
                    @php
                        $oldPrices = old('branch_prices', []);
                        $value = $oldPrices[$branch->id] ?? $branchPrices->get($branch->id);
                    @endphp
                    <div class="col-md-4">
                        <label for="branch_price_{{ $branch->id }}" class="form-label">{{ $branch->name }}</label>
                        <input type="number" class="form-control @error('branch_prices.'.$branch->id) is-invalid @enderror" id="branch_price_{{ $branch->id }}" name="branch_prices[{{ $branch->id }}]" value="{{ $value }}" min="0" step="0.01">
                        @error('branch_prices.'.$branch->id)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($agents->isNotEmpty())
        <div class="mt-4">
            <h6 class="mb-1">Agent Prices</h6>
            <p class="text-muted small">Minimum price and commission are set separately for each shop. The MRP on this product is the highest price an agent can sell at. Leave a row blank if that shop does not sell this product.</p>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Shop</th>
                            <th>Minimum Price</th>
                            <th>Commission %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($agents as $agent)
                            @php
                                $oldTerms = old('agent_terms.'.$agent->id, []);
                                $saved = $agentTerms->get($agent->id);
                                $minimum = $oldTerms['minimum_price'] ?? $saved?->minimum_price;
                                $commission = $oldTerms['commission_percent'] ?? $saved?->commission_percent;
                            @endphp
                            <tr>
                                <td>{{ $agent->name }}</td>
                                <td style="max-width: 180px;">
                                    <input type="number" class="form-control @error('agent_terms.'.$agent->id.'.minimum_price') is-invalid @enderror" name="agent_terms[{{ $agent->id }}][minimum_price]" value="{{ $minimum }}" min="0" step="0.01">
                                    @error('agent_terms.'.$agent->id.'.minimum_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                                <td style="max-width: 160px;">
                                    <input type="number" class="form-control @error('agent_terms.'.$agent->id.'.commission_percent') is-invalid @enderror" name="agent_terms[{{ $agent->id }}][commission_percent]" value="{{ $commission }}" min="0" max="100" step="0.01">
                                    @error('agent_terms.'.$agent->id.'.commission_percent')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-success">Save Product</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>
