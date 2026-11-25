@php
    $inventoryTabs = [
        [
            'label' => 'Warehouse Stock',
            'url' => route('admin.stocks.index'),
            'active' => request()->routeIs('admin.stocks.*'),
        ],
        [
            'label' => 'Branch Stock',
            'url' => route('admin.branch-stocks.index'),
            'active' => request()->routeIs('admin.branch-stocks.*'),
        ],
        [
            'label' => 'Transfers',
            'url' => route('admin.transfers.index'),
            'active' => request()->routeIs('admin.transfers.*'),
        ],
        [
            'label' => 'Stock Requests',
            'url' => route('admin.stock-requests.index'),
            'active' => request()->routeIs('admin.stock-requests.*'),
        ],
    ];
@endphp

<ul class="nav nav-tabs mb-3 flex-wrap">
    @foreach ($inventoryTabs as $tab)
        <li class="nav-item">
            <a class="nav-link {{ $tab['active'] ? 'active' : '' }}" href="{{ $tab['url'] }}">{{ $tab['label'] }}</a>
        </li>
    @endforeach
</ul>
