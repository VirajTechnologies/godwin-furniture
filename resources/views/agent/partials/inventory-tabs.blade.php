@php
    $isManager = auth()->user()?->isAgentManager();

    $inventoryTabs = [
        [
            'label' => 'Stock',
            'url' => route('agent.stock.index'),
            'active' => request()->routeIs('agent.stock.*'),
            'show' => true,
        ],
        [
            'label' => 'Transfers',
            'url' => route('agent.transfers.index'),
            'active' => request()->routeIs('agent.transfers.*'),
            'show' => $isManager,
        ],
        [
            'label' => 'Stock Requests',
            'url' => route('agent.stock-requests.index'),
            'active' => request()->routeIs('agent.stock-requests.*'),
            'show' => $isManager,
        ],
        [
            'label' => 'Custom Requests',
            'url' => route('agent.custom-requests.index'),
            'active' => request()->routeIs('agent.custom-requests.*'),
            'show' => $isManager,
        ],
    ];

    $inventoryTabs = array_values(array_filter($inventoryTabs, fn ($tab) => $tab['show']));
@endphp

@if (count($inventoryTabs) > 1)
    <ul class="nav nav-tabs mb-3 flex-wrap">
        @foreach ($inventoryTabs as $tab)
            <li class="nav-item">
                <a class="nav-link {{ $tab['active'] ? 'active' : '' }}" href="{{ $tab['url'] }}">{{ $tab['label'] }}</a>
            </li>
        @endforeach
    </ul>
@endif
