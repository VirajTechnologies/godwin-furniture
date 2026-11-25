@extends('layouts.admin')

@section('title', 'Config Options')
@section('page_title', 'Config Options')

@section('breadcrumb')
    <li class="breadcrumb-item active">Config Options</li>
@endsection

@section('content')
    @php
        $hasFilters = $filters['q'] !== ''
            || $filters['status'] !== ''
            || $filters['family_key'] !== ''
            || $filters['field_key'] !== '';
    @endphp

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Configuration Options</h5>
                    <a href="{{ route('admin.catalog-config-options.create') }}" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> Add Option
                    </a>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.catalog-config-options.index') }}" class="row g-2 mb-4">
                        <div class="col-md-3">
                            <label for="catalog_config_option_filter_q" class="form-label">Search</label>
                            <input type="text" class="form-control" id="catalog_config_option_filter_q" name="q" value="{{ $filters['q'] }}" placeholder="Option name">
                        </div>
                        <div class="col-md-2">
                            <label for="catalog_config_option_filter_family" class="form-label">Family</label>
                            <select class="form-select" id="catalog_config_option_filter_family" name="family_key" onchange="this.form.submit()">
                                <option value="" @selected($filters['family_key'] === '')>All</option>
                                @foreach ($filterFamilies as $key => $family)
                                    <option value="{{ $key }}" @selected($filters['family_key'] === $key)>{{ $family['label'] ?? $key }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="catalog_config_option_filter_field" class="form-label">Field</label>
                            <select class="form-select" id="catalog_config_option_filter_field" name="field_key">
                                <option value="" @selected($filters['field_key'] === '')>All</option>
                                @foreach ($filterFields as $key => $label)
                                    <option value="{{ $key }}" @selected($filters['field_key'] === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="catalog_config_option_filter_status" class="form-label">Status</label>
                            <select class="form-select" id="catalog_config_option_filter_status" name="status">
                                <option value="" @selected($filters['status'] === '')>All</option>
                                <option value="active" @selected($filters['status'] === 'active')>Active</option>
                                <option value="inactive" @selected($filters['status'] === 'inactive')>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            @if ($hasFilters)
                                <a href="{{ route('admin.catalog-config-options.index') }}" class="btn btn-light">Clear</a>
                            @endif
                        </div>
                    </form>

                    @if ($catalogConfigOptions->isEmpty())
                        <div class="text-center py-5">
                            @if ($hasFilters)
                                <h5 class="mb-2">No options match</h5>
                                <a href="{{ route('admin.catalog-config-options.index') }}" class="btn btn-light">Clear Filters</a>
                            @else
                                <h5 class="mb-2">No configuration options yet</h5>
                                <a href="{{ route('admin.catalog-config-options.create') }}" class="btn btn-success">Add Option</a>
                            @endif
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Family</th>
                                        <th>Field</th>
                                        <th>Option</th>
                                        <th class="text-end">Sort</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($catalogConfigOptions as $option)
                                        @php
                                            $fieldLabel = \App\Support\Catalog::selectFieldsForFamily($option->family_key)[$option->field_key] ?? $option->field_key;
                                            $familyLabel = $filterFamilies[$option->family_key]['label'] ?? $option->family_key;
                                        @endphp
                                        <tr>
                                            <td>{{ $catalogConfigOptions->firstItem() + $loop->index }}</td>
                                            <td>{{ $familyLabel }}</td>
                                            <td>{{ $fieldLabel }}</td>
                                            <td class="fw-medium">{{ $option->name }}</td>
                                            <td class="text-end">{{ $option->sort_order }}</td>
                                            <td>
                                                @if ($option->isActive())
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                                    @include('admin.partials.edit-icon', ['url' => route('admin.catalog-config-options.edit', $option)])
                                                    @include('admin.partials.status-actions', [
                                                        'record' => $option,
                                                        'activateUrl' => route('admin.catalog-config-options.status', $option),
                                                    ])
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $catalogConfigOptions->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
