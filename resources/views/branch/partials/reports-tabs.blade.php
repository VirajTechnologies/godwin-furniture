@php
    $isManager = auth()->user()?->isBranchManager();
    $isPrimary = (bool) auth()->user()?->employee?->branch?->is_primary;
    $dateQuery = request()->only(['from', 'to']);

    $reportsTabs = [
        [
            'label' => 'Sales By Product',
            'url' => route('branch.reports.sales-by-product', $dateQuery),
            'active' => request()->routeIs('branch.reports.sales-by-product'),
            'show' => true,
        ],
        [
            'label' => 'Stock On Hand',
            'url' => route('branch.reports.stock'),
            'active' => request()->routeIs('branch.reports.stock'),
            'show' => true,
        ],
        [
            'label' => 'Transfers',
            'url' => route('branch.reports.transfers', $dateQuery),
            'active' => request()->routeIs('branch.reports.transfers'),
            'show' => $isManager,
        ],
        [
            'label' => 'Stock Requests',
            'url' => route('branch.reports.stock-requests', $dateQuery),
            'active' => request()->routeIs('branch.reports.stock-requests'),
            'show' => $isManager,
        ],
        [
            'label' => 'Custom Requests',
            'url' => route('branch.reports.custom-requests', $dateQuery),
            'active' => request()->routeIs('branch.reports.custom-requests'),
            'show' => $isManager && $isPrimary,
        ],
    ];

    $reportsTabs = array_values(array_filter($reportsTabs, fn ($tab) => $tab['show']));
@endphp

<ul class="nav nav-tabs mb-3 flex-wrap">
    @foreach ($reportsTabs as $tab)
        <li class="nav-item">
            <a class="nav-link {{ $tab['active'] ? 'active' : '' }}" href="{{ $tab['url'] }}">{{ $tab['label'] }}</a>
        </li>
    @endforeach
</ul>
