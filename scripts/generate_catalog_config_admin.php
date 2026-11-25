<?php

$root = dirname(__DIR__);

function write(string $path, string $contents): void
{
    $dir = dirname($path);
    if (! is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($path, $contents);
    echo "Wrote {$path}\n";
}

// --- Simple masters: colours, finishes, upholsteries ---

$simple = [
    [
        'model' => 'ProductColour',
        'var' => 'productColour',
        'vars' => 'productColours',
        'route' => 'product-colours',
        'title' => 'Colours',
        'singular' => 'Colour',
        'folder' => 'product-colours',
        'controller' => 'ProductColourController',
        'request' => 'ProductColourRequest',
        'table' => 'product_colours',
        'extraFields' => true, // hex
    ],
    [
        'model' => 'ProductFinish',
        'var' => 'productFinish',
        'vars' => 'productFinishes',
        'route' => 'product-finishes',
        'title' => 'Finishes',
        'singular' => 'Finish',
        'folder' => 'product-finishes',
        'controller' => 'ProductFinishController',
        'request' => 'ProductFinishRequest',
        'table' => 'product_finishes',
        'extraFields' => false,
    ],
    [
        'model' => 'ProductUpholstery',
        'var' => 'productUpholstery',
        'vars' => 'productUpholsteries',
        'route' => 'product-upholsteries',
        'title' => 'Upholstery',
        'singular' => 'Upholstery',
        'folder' => 'product-upholsteries',
        'controller' => 'ProductUpholsteryController',
        'request' => 'ProductUpholsteryRequest',
        'table' => 'product_upholsteries',
        'extraFields' => false,
    ],
];

foreach ($simple as $m) {
    $hexRules = $m['extraFields']
        ? "            'hex' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],\n"
        : '';
    $createDefaults = $m['extraFields']
        ? "['status' => {$m['model']}::STATUS_ACTIVE, 'sort_order' => 0, 'hex' => '#888888']"
        : "['status' => {$m['model']}::STATUS_ACTIVE, 'sort_order' => 0]";

    write("{$root}/app/Http/Requests/Admin/{$m['request']}.php", <<<PHP
<?php

namespace App\Http\Requests\Admin;

use App\Models\\{$m['model']};
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class {$m['request']} extends FormRequest
{
    public function authorize(): bool
    {
        return \$this->user()?->isSuperAdmin() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('{$m['table']}', 'name')->ignore(\$this->route('{$m['var']}'))],
{$hexRules}            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in([{$m['model']}::STATUS_ACTIVE, {$m['model']}::STATUS_INACTIVE])],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! \$this->filled('sort_order')) {
            \$this->merge(['sort_order' => 0]);
        }
    }
}

PHP);

    write("{$root}/app/Http/Controllers/Admin/{$m['controller']}.php", <<<PHP
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\FiltersCatalogMasters;
use App\Http\Controllers\Admin\Concerns\UpdatesActiveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\\{$m['request']};
use App\Models\\{$m['model']};
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class {$m['controller']} extends Controller
{
    use FiltersCatalogMasters, UpdatesActiveStatus;

    public function index(Request \$request): View
    {
        \$filters = \$this->catalogFilters(\$request);

        return view('admin.{$m['folder']}.index', [
            'filters' => \$filters,
            '{$m['vars']}' => \$this->applyCatalogFilters({$m['model']}::query(), \$filters)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.{$m['folder']}.create', [
            '{$m['var']}' => new {$m['model']}({$createDefaults}),
        ]);
    }

    public function store({$m['request']} \$request): RedirectResponse
    {
        {$m['model']}::query()->create(\$request->validated());

        return redirect()->route('admin.{$m['route']}.index')->with('success', '{$m['singular']} saved.');
    }

    public function edit({$m['model']} \${$m['var']}): View
    {
        return view('admin.{$m['folder']}.edit', [
            '{$m['var']}' => \${$m['var']},
        ]);
    }

    public function update({$m['request']} \$request, {$m['model']} \${$m['var']}): RedirectResponse
    {
        \${$m['var']}->update(\$request->validated());

        return redirect()->route('admin.{$m['route']}.index')->with('success', '{$m['singular']} updated.');
    }

    public function updateStatus(Request \$request, {$m['model']} \${$m['var']}): RedirectResponse
    {
        return \$this->updateActiveStatus(\$request, \${$m['var']}, \${$m['var']}->name);
    }
}

PHP);

    $hexForm = $m['extraFields'] ? <<<'BLADE'
        <div class="col-md-4">
            <label for="hex" class="form-label required">Hex</label>
            <input type="color" class="form-control form-control-color @error('hex') is-invalid @enderror" id="hex" name="hex" value="{{ old('hex', $RECORD->hex ?? '#888888') }}" title="Colour" required>
            @error('hex')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
BLADE : '';
    $hexForm = str_replace('$RECORD', '$'.$m['var'], $hexForm);

    $hexColHead = $m['extraFields'] ? "                                        <th>Hex</th>\n" : '';
    $hexColBody = $m['extraFields']
        ? "                                            <td><span class=\"d-inline-block rounded border\" style=\"width:18px;height:18px;background:{{ \$item->hex }};vertical-align:middle\"></span> <code>{{ \$item->hex }}</code></td>\n"
        : '';

    write("{$root}/resources/views/admin/{$m['folder']}/_form.blade.php", <<<BLADE
@php
    \$isEdit = \${$m['var']}->exists;
@endphp

<form method="POST" action="{{ \$isEdit ? route('admin.{$m['route']}.update', \${$m['var']}) : route('admin.{$m['route']}.store') }}">
    @csrf
    @if (\$isEdit)
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-md-8">
            <label for="{$m['var']}_name" class="form-label required">Name</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="{$m['var']}_name" name="name" value="{{ old('name', \${$m['var']}->name) }}" required>
            @error('name')
                <div class="invalid-feedback">{{ \$message }}</div>
            @enderror
        </div>
{$hexForm}
        <div class="col-md-4">
            <label for="{$m['var']}_sort_order" class="form-label">Sort Order</label>
            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="{$m['var']}_sort_order" name="sort_order" value="{{ old('sort_order', \${$m['var']}->sort_order ?? 0) }}" min="0" step="1">
            @error('sort_order')
                <div class="invalid-feedback">{{ \$message }}</div>
            @enderror
        </div>
        @include('admin.partials.record-status', ['statusId' => '{$m['var']}_record_status', 'record' => \${$m['var']}])
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-success">Save {$m['singular']}</button>
        <a href="{{ route('admin.{$m['route']}.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>
BLADE);

    foreach (['create' => 'Add', 'edit' => 'Edit'] as $page => $label) {
        write("{$root}/resources/views/admin/{$m['folder']}/{$page}.blade.php", <<<BLADE
@extends('layouts.admin')

@section('title', '{$label} {$m['singular']}')
@section('page_title', '{$label} {$m['singular']}')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.{$m['route']}.index') }}">{$m['title']}</a></li>
    <li class="breadcrumb-item active">{$label}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    @include('admin.{$m['folder']}._form')
                </div>
            </div>
        </div>
    </div>
@endsection
BLADE);
    }

    write("{$root}/resources/views/admin/{$m['folder']}/index.blade.php", <<<BLADE
@extends('layouts.admin')

@section('title', '{$m['title']}')
@section('page_title', '{$m['title']}')

@section('breadcrumb')
    <li class="breadcrumb-item active">{$m['title']}</li>
@endsection

@section('content')
    @php
        \$hasFilters = \$filters['q'] !== '' || \$filters['status'] !== '';
    @endphp

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">{$m['title']}</h5>
                    <a href="{{ route('admin.{$m['route']}.create') }}" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> Add {$m['singular']}
                    </a>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.{$m['route']}.index') }}" class="row g-2 mb-4">
                        <div class="col-md-4">
                            <label for="{$m['var']}_filter_q" class="form-label">Search</label>
                            <input type="text" class="form-control" id="{$m['var']}_filter_q" name="q" value="{{ \$filters['q'] }}" placeholder="Name">
                        </div>
                        <div class="col-md-3">
                            <label for="{$m['var']}_filter_status" class="form-label">Status</label>
                            <select class="form-select" id="{$m['var']}_filter_status" name="status">
                                <option value="" @selected(\$filters['status'] === '')>All</option>
                                <option value="active" @selected(\$filters['status'] === 'active')>Active</option>
                                <option value="inactive" @selected(\$filters['status'] === 'inactive')>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            @if (\$hasFilters)
                                <a href="{{ route('admin.{$m['route']}.index') }}" class="btn btn-light">Clear</a>
                            @endif
                        </div>
                    </form>

                    @if (\${$m['vars']}->isEmpty())
                        <div class="text-center py-5">
                            @if (\$hasFilters)
                                <h5 class="mb-2">No matches</h5>
                                <a href="{{ route('admin.{$m['route']}.index') }}" class="btn btn-light">Clear Filters</a>
                            @else
                                <h5 class="mb-2">No {$m['title']} yet</h5>
                                <a href="{{ route('admin.{$m['route']}.create') }}" class="btn btn-success">Add {$m['singular']}</a>
                            @endif
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Name</th>
{$hexColHead}                                        <th class="text-end">Sort</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (\${$m['vars']} as \$item)
                                        <tr>
                                            <td>{{ \${$m['vars']}->firstItem() + \$loop->index }}</td>
                                            <td class="fw-medium">{{ \$item->name }}</td>
{$hexColBody}                                            <td class="text-end">{{ \$item->sort_order }}</td>
                                            <td>
                                                @if (\$item->isActive())
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                                    @include('admin.partials.edit-icon', ['url' => route('admin.{$m['route']}.edit', \$item)])
                                                    @include('admin.partials.status-actions', [
                                                        'record' => \$item,
                                                        'activateUrl' => route('admin.{$m['route']}.status', \$item),
                                                    ])
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ \${$m['vars']}->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
BLADE);
}

echo "Simple masters done.\n";
