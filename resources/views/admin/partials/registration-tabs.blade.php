@php
    $registrationTabs = [
        [
            'label' => 'Branch',
            'url' => route('admin.branches.index'),
            'active' => request()->routeIs('admin.branches.*'),
        ],
        [
            'label' => 'Warehouse',
            'url' => route('admin.warehouses.index'),
            'active' => request()->routeIs('admin.warehouses.*'),
        ],
        [
            'label' => 'Employee',
            'url' => route('admin.employees.index'),
            'active' => request()->routeIs('admin.employees.*'),
        ],
        [
            'label' => 'Agent',
            'url' => route('admin.agents.index'),
            'active' => request()->routeIs('admin.agents.*'),
        ],
    ];
@endphp

<ul class="nav nav-tabs mb-3 flex-wrap">
    @foreach ($registrationTabs as $tab)
        <li class="nav-item">
            <a class="nav-link {{ $tab['active'] ? 'active' : '' }}" href="{{ $tab['url'] }}">{{ $tab['label'] }}</a>
        </li>
    @endforeach
</ul>
