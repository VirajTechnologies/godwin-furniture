{{-- Desktop sticky mega-menu: rooms from $storeMenu --}}
{{-- Room name navigates; chevron toggles the panel (touch-friendly). Hover still opens on desktop. --}}
<nav class="sticky-top luxury-menubar-bg py-2 d-none d-lg-block" style="z-index: 1050;">
    <div class="container-fluid px-4 px-xl-5">
        <ul class="nav justify-content-center align-items-center gap-3 gap-xl-4 main-nav-luxury position-relative m-0">
            <li class="nav-item">
                <a class="nav-link text-danger fw-bold d-flex align-items-center" href="{{ route('store.catalog', ['sort' => 'popular']) }}">
                    <span class="nav-sale-icon-badge me-2"><i class="fas fa-percent"></i></span> Sale 🔥
                </a>
            </li>

            @foreach ($storeMenu ?? [] as $room)
                @php
                    $groups = $room->children->groupBy(fn ($child) => $child->menu_group ?: 'Shop');
                    $groupCount = max(1, $groups->count());
                    $colClass = match (true) {
                        $groupCount >= 3 => 'col-lg-3',
                        $groupCount === 2 => 'col-lg-4',
                        default => 'col-lg-6',
                    };
                    $roomActive = request('category') === $room->slug
                        || $room->children->contains(fn ($child) => $child->slug === request('category'));
                    $menuId = 'store-mega-'.$room->id;
                @endphp
                <li class="nav-item dropdown position-static">
                    <div class="nav-room-item d-inline-flex align-items-center {{ $roomActive ? 'active' : '' }}">
                        <a class="nav-link d-flex align-items-center {{ $roomActive ? 'active' : '' }}"
                           href="{{ route('store.catalog', ['category' => $room->slug]) }}">
                            @if ($room->image_url)
                                <img src="{{ $room->image_url }}" class="nav-heading-thumb me-2" alt="{{ $room->name }}">
                            @endif
                            {{ $room->name }}
                        </a>
                        <button type="button"
                                class="nav-room-caret"
                                id="{{ $menuId }}-toggle"
                                data-bs-toggle="dropdown"
                                data-bs-target="#{{ $menuId }}"
                                data-bs-auto-close="outside"
                                aria-expanded="false"
                                aria-controls="{{ $menuId }}"
                                aria-label="Open {{ $room->name }} menu">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                    <div class="dropdown-menu mega-menu-panel border-0 shadow-lg" id="{{ $menuId }}" aria-labelledby="{{ $menuId }}-toggle">
                        <div class="row g-4 align-items-start">
                            @forelse ($groups as $groupName => $children)
                                <div class="{{ $colClass }}">
                                    <h6 class="mega-menu-title d-flex align-items-center gap-2">
                                        @if ($room->image_url)
                                            <img src="{{ $room->image_url }}" class="mega-title-thumb" alt="{{ $groupName }}">
                                        @endif
                                        {{ $groupName }}
                                    </h6>
                                    <ul class="mega-menu-list">
                                        @foreach ($children as $child)
                                            <li>
                                                <a href="{{ route('store.catalog', ['category' => $child->slug]) }}" class="{{ request('category') === $child->slug ? 'fw-bold text-amber' : '' }}">
                                                    {{ $child->name }}
                                                    <i class="fas fa-chevron-right small text-muted"></i>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p class="text-muted small mb-2">No subcategories yet.</p>
                                    <a href="{{ route('store.catalog', ['category' => $room->slug]) }}" class="small text-amber fw-semibold">Browse {{ $room->name }} →</a>
                                </div>
                            @endforelse

                            @if ($groups->isNotEmpty())
                                <div class="{{ $colClass }}">
                                    <div class="mega-menu-banner rounded-3 overflow-hidden position-relative">
                                        <img src="{{ $room->image_url ?: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=500' }}" alt="{{ $room->name }}" class="img-fluid w-100" style="height: 210px; object-fit: cover;">
                                        <div class="position-absolute bottom-0 start-0 end-0 p-3 bg-dark bg-opacity-85 text-white">
                                            <span class="badge bg-danger text-white mb-1 fw-bold" style="font-size: 10px;"><i class="fas fa-bolt me-1"></i> SHOP NOW</span>
                                            <h6 class="m-0 text-white font-heading fw-bold">Explore {{ $room->name }}</h6>
                                            <a href="{{ route('store.catalog', ['category' => $room->slug]) }}" class="small text-warning text-decoration-none fw-semibold">View all →</a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</nav>
