@php
    $mastersTabs = [
        [
            'label' => 'States',
            'url' => route('admin.states.index'),
            'active' => request()->routeIs('admin.states.*'),
        ],
        [
            'label' => 'Districts',
            'url' => route('admin.districts.index'),
            'active' => request()->routeIs('admin.districts.*'),
        ],
        [
            'label' => 'Cities',
            'url' => route('admin.cities.index'),
            'active' => request()->routeIs('admin.cities.*'),
        ],
        [
            'label' => 'Business Groups',
            'url' => route('admin.business-groups.index'),
            'active' => request()->routeIs('admin.business-groups.*'),
        ],
        [
            'label' => 'Projects',
            'url' => route('admin.project-categories.index'),
            'active' => request()->routeIs('admin.project-categories.*'),
        ],
        [
            'label' => 'Rooms',
            'url' => route('admin.catalog-rooms.index'),
            'active' => request()->routeIs('admin.catalog-rooms.*'),
        ],
        [
            'label' => 'Product Categories',
            'url' => route('admin.catalog-product-categories.index'),
            'active' => request()->routeIs('admin.catalog-product-categories.*'),
        ],
        [
            'label' => 'Design Types',
            'url' => route('admin.design-types.index'),
            'active' => request()->routeIs('admin.design-types.*'),
        ],
        [
            'label' => 'Product Models',
            'url' => route('admin.product-models.index'),
            'active' => request()->routeIs('admin.product-models.*'),
        ],
        [
            'label' => 'Config Options',
            'url' => route('admin.catalog-config-options.index'),
            'active' => request()->routeIs('admin.catalog-config-options.*'),
        ],
        [
            'label' => 'Colours',
            'url' => route('admin.product-colours.index'),
            'active' => request()->routeIs('admin.product-colours.*'),
        ],
        [
            'label' => 'Finishes',
            'url' => route('admin.product-finishes.index'),
            'active' => request()->routeIs('admin.product-finishes.*'),
        ],
        [
            'label' => 'Upholstery',
            'url' => route('admin.product-upholsteries.index'),
            'active' => request()->routeIs('admin.product-upholsteries.*'),
        ],
    ];
@endphp

<ul class="nav nav-tabs mb-3 flex-wrap">
    @foreach ($mastersTabs as $tab)
        <li class="nav-item">
            <a class="nav-link {{ $tab['active'] ? 'active' : '' }}" href="{{ $tab['url'] }}">{{ $tab['label'] }}</a>
        </li>
    @endforeach
</ul>
