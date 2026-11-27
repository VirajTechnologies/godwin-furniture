@php
    $reportsTabs = [
        [
            'label' => 'Overview',
            'url' => route('admin.reports.overview', request()->only(['from', 'to'])),
            'active' => request()->routeIs('admin.reports.overview', 'admin.reports.export'),
        ],
        [
            'label' => 'Sales By Branch',
            'url' => route('admin.reports.sales-by-branch'),
            'active' => request()->routeIs('admin.reports.sales-by-branch'),
        ],
        [
            'label' => 'Sales By Product',
            'url' => route('admin.reports.sales-by-product'),
            'active' => request()->routeIs('admin.reports.sales-by-product'),
        ],
        [
            'label' => 'Stock On Hand',
            'url' => route('admin.reports.stock'),
            'active' => request()->routeIs('admin.reports.stock'),
        ],
        [
            'label' => 'Transfers',
            'url' => route('admin.reports.transfers'),
            'active' => request()->routeIs('admin.reports.transfers'),
        ],
        [
            'label' => 'Stock Requests',
            'url' => route('admin.reports.stock-requests'),
            'active' => request()->routeIs('admin.reports.stock-requests'),
        ],
        [
            'label' => 'Custom Requests',
            'url' => route('admin.reports.custom-requests'),
            'active' => request()->routeIs('admin.reports.custom-requests'),
        ],
    ];
@endphp

<ul class="nav nav-tabs mb-3 flex-wrap">
    @foreach ($reportsTabs as $tab)
        <li class="nav-item">
            <a class="nav-link {{ $tab['active'] ? 'active' : '' }}" href="{{ $tab['url'] }}">{{ $tab['label'] }}</a>
        </li>
    @endforeach
</ul>
