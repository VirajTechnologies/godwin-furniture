@php
    $activeChannel = isset($order)
        ? (string) $order->channel
        : (string) ($channel ?? request()->query('channel', ''));

    $salesTabs = [
        [
            'label' => 'All',
            'url' => route('admin.orders.index'),
            'active' => $activeChannel === '' && ! isset($order),
        ],
        [
            'label' => 'Online',
            'url' => route('admin.orders.index', ['channel' => 'online']),
            'active' => $activeChannel === 'online',
        ],
        [
            'label' => 'Branch',
            'url' => route('admin.orders.index', ['channel' => 'branch']),
            'active' => $activeChannel === 'branch',
        ],
        [
            'label' => 'Agent',
            'url' => route('admin.orders.index', ['channel' => 'agent']),
            'active' => $activeChannel === 'agent',
        ],
    ];
@endphp

<ul class="nav nav-tabs mb-3 flex-wrap">
    @foreach ($salesTabs as $tab)
        <li class="nav-item">
            <a class="nav-link {{ $tab['active'] ? 'active' : '' }}" href="{{ $tab['url'] }}">{{ $tab['label'] }}</a>
        </li>
    @endforeach
</ul>
