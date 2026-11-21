{{-- Desktop sticky navigation bar with compact anchored dropdown submenus --}}
{{-- Room name navigates; chevron toggles the panel (touch-friendly). Hover still opens on desktop. --}}
<nav class="sticky-top luxury-menubar-bg d-none d-lg-block" style="z-index: 1050;">
    <div class="container-fluid px-4 px-xl-5">
        <ul class="nav justify-content-center align-items-center main-nav-luxury position-relative m-0">
            <li class="nav-item">
                <a class="nav-link nav-sale-link text-danger fw-bold d-inline-flex align-items-center" href="{{ route('store.catalog', ['sort' => 'popular']) }}">
                    <span class="nav-sale-icon-badge"><i class="fas fa-percent"></i></span>
                    <span>Sale</span>
                </a>
            </li>

            @foreach ($storeMenu ?? [] as $room)
                @php
                    $groups = $room->children->groupBy(fn ($child) => $child->menu_group ?: 'Shop');
                    $groupCount = max(1, $groups->count());
                    $totalChildren = $room->children->count();
                    $hasCustomGroups = $groupCount > 1 || ($groups->keys()->first() && $groups->keys()->first() !== 'Shop');
                    $useTwoCols = ! $hasCustomGroups && $totalChildren > 7;
                    $roomActive = request('category') === $room->slug
                        || $room->children->contains(fn ($child) => $child->slug === request('category'));
                    $menuId = 'store-mega-'.$room->id;
                    $alignRight = $loop->remaining < 2;
                    $roomIcon = match (true) {
                        str_contains(strtolower($room->name), 'living') => 'fa-couch',
                        str_contains(strtolower($room->name), 'bed') => 'fa-bed',
                        str_contains(strtolower($room->name), 'dining') => 'fa-utensils',
                        str_contains(strtolower($room->name), 'office') => 'fa-briefcase',
                        str_contains(strtolower($room->name), 'outdoor') => 'fa-umbrella-beach',
                        str_contains(strtolower($room->name), 'kids') => 'fa-shapes',
                        str_contains(strtolower($room->name), 'study') => 'fa-book-open',
                        str_contains(strtolower($room->name), 'entry') => 'fa-door-open',
                        str_contains(strtolower($room->name), 'kitchen') => 'fa-sink',
                        default => 'fa-layer-group',
                    };
                @endphp
                <li class="nav-item dropdown">
                    <div class="nav-room-item d-inline-flex align-items-center {{ $roomActive ? 'active' : '' }}">
                        <a class="nav-link d-inline-flex align-items-center {{ $roomActive ? 'active' : '' }}"
                           href="{{ route('store.catalog', ['category' => $room->slug]) }}">
                            @if ($room->image_url)
                                <img src="{{ $room->image_url }}" class="nav-heading-thumb me-2" alt="{{ $room->name }}">
                            @endif
                            <span>{{ $room->name }}</span>
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

                    <div class="dropdown-menu mega-menu-panel {{ $useTwoCols ? 'mega-panel-2col' : '' }} {{ $hasCustomGroups && $groupCount > 1 ? 'mega-panel-grouped' : '' }} {{ $alignRight ? 'mega-panel-end' : '' }} border-0"
                         id="{{ $menuId }}"
                         aria-labelledby="{{ $menuId }}-toggle">
                        {{-- Compact Dropdown Header --}}
                        <div class="mega-compact-head">
                            <div class="d-flex align-items-center gap-2">
                                <span class="mega-compact-icon">
                                    <i class="fas {{ $roomIcon }}"></i>
                                </span>
                                <span class="mega-compact-title">{{ $room->name }}</span>
                            </div>
                            <span class="mega-compact-badge">{{ $totalChildren }}</span>
                        </div>

                        {{-- Compact Subcategory List --}}
                        <div class="mega-compact-body">
                            @if ($groups->isNotEmpty())
                                <div class="mega-groups-wrap {{ $groupCount > 1 ? 'is-multi-group' : '' }}">
                                    @foreach ($groups as $groupName => $children)
                                        <div class="mega-group-col">
                                            @if ($hasCustomGroups)
                                                <div class="mega-menu-title">{{ $groupName }}</div>
                                            @endif
                                            <ul class="mega-menu-list {{ $useTwoCols ? 'mega-list-2col' : '' }}">
                                                @foreach ($children as $child)
                                                    <li>
                                                        <a href="{{ route('store.catalog', ['category' => $child->slug]) }}"
                                                           class="mega-menu-link {{ request('category') === $child->slug ? 'active fw-bold text-amber' : '' }}">
                                                            <span>{{ $child->name }}</span>
                                                            <i class="fas fa-chevron-right mega-link-arrow"></i>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="px-2 py-2">
                                    <p class="text-muted small mb-1">No subcategories yet.</p>
                                    <a href="{{ route('store.catalog', ['category' => $room->slug]) }}" class="small text-amber fw-semibold text-decoration-none">Browse {{ $room->name }} →</a>
                                </div>
                            @endif
                        </div>

                        {{-- Compact Footer Link --}}
                        <div class="mega-compact-foot">
                            <a href="{{ route('store.catalog', ['category' => $room->slug]) }}" class="mega-foot-action">
                                <span>View All {{ $room->name }}</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</nav>
