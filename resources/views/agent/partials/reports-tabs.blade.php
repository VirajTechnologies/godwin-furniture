@php
    $isManager = auth()->user()?->isAgentManager();
    $dateQuery = request()->only(['from', 'to']);

    $reportsTabs = [
        [
            'label' => 'Sales By Product',
            'url' => route('agent.reports.sales-by-product', $dateQuery),
            'active' => request()->routeIs('agent.reports.sales-by-product'),
            'show' => true,
        ],
        [
            'label' => 'Stock On Hand',
            'url' => route('agent.reports.stock'),
            'active' => request()->routeIs('agent.reports.stock'),
            'show' => true,
        ],
        [
            'label' => 'Transfers',
            'url' => route('agent.reports.transfers', $dateQuery),
            'active' => request()->routeIs('agent.reports.transfers'),
            'show' => $isManager,
        ],
        [
            'label' => 'Stock Requests',
            'url' => route('agent.reports.stock-requests', $dateQuery),
            'active' => request()->routeIs('agent.reports.stock-requests'),
            'show' => $isManager,
        ],
        [
            'label' => 'Custom Requests',
            'url' => route('agent.reports.custom-requests', $dateQuery),
            'active' => request()->routeIs('agent.reports.custom-requests'),
            'show' => $isManager,
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
