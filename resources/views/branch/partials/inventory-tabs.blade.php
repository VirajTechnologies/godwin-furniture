@php
    $isManager = auth()->user()?->isBranchManager();
    $isPrimary = (bool) auth()->user()?->employee?->branch?->is_primary;

    $inventoryTabs = [
        [
            'label' => 'Stock',
            'url' => route('branch.stock.index'),
            'active' => request()->routeIs('branch.stock.*'),
            'show' => true,
        ],
        [
            'label' => 'Transfers',
            'url' => route('branch.transfers.index'),
            'active' => request()->routeIs('branch.transfers.*'),
            'show' => $isManager,
        ],
        [
            'label' => 'Stock Requests',
            'url' => route('branch.stock-requests.index'),
            'active' => request()->routeIs('branch.stock-requests.*'),
            'show' => $isManager,
        ],
        [
            'label' => 'Custom Requests',
            'url' => route('branch.custom-requests.index'),
            'active' => request()->routeIs('branch.custom-requests.*'),
            'show' => $isManager && $isPrimary,
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
