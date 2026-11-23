{{-- Unified GODWIN Announcement Bar + Single-Row Sticky Navigation Bar --}}
<div class="gw-ann" id="gwAnnBar">Explore our latest collections.</div>

<nav class="gw-nav" id="gwNav">
    <button class="gw-fb"
            id="gwMenuToggle"
            type="button"
            aria-expanded="false"
            aria-controls="gwMenuBar"
            aria-label="Toggle navigation menu">
        <i class="fas fa-bars" aria-hidden="true"></i>
    </button>

    <a class="gw-logo d-inline-flex align-items-center" href="{{ route('store.home') }}">
        <img src="{{ asset('store/images/logo.png') }}" alt="GODWIN" class="gw-logo-img">
    </a>

    <div class="gw-menu" id="gwMenuBar">
        <div class="gw-menu-item">
            <a href="{{ route('store.home') }}" class="{{ request()->routeIs('store.home') ? 'is-active' : '' }}">HOME</a>
        </div>

        @forelse ($storeMenu ?? [] as $room)
            @php
                $groups = $room->children->groupBy(fn ($child) => $child->menu_group ?: 'Shop');
                $groupCount = max(1, $groups->count());
                $hasCustomGroups = $groupCount > 1 || ($groups->keys()->first() && $groups->keys()->first() !== 'Shop');
                $roomActive = request('category') === $room->slug
                    || $room->children->contains(fn ($child) => $child->slug === request('category'));
                $menuId = 'store-mega-'.$room->id;
            @endphp
            <div class="gw-menu-item">
                <div class="gw-menu-trigger">
                    <a href="{{ route('store.catalog', ['category' => $room->slug]) }}" class="{{ $roomActive ? 'is-active' : '' }}">
                        {{ strcasecmp($room->name, 'Dining Room') === 0 ? 'Dining' : $room->name }}
                    </a>
                    <button type="button"
                            class="gw-caret-btn"
                            id="{{ $menuId }}-toggle"
                            aria-expanded="false"
                            aria-controls="{{ $menuId }}"
                            aria-label="Open {{ $room->name }} menu"
                            onclick="var item=this.closest('.gw-menu-item');var open=item.classList.toggle('dd-open');this.setAttribute('aria-expanded',open?'true':'false')">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>

                @if ($room->children->isNotEmpty())
                    <div class="gw-dd" id="{{ $menuId }}" aria-labelledby="{{ $menuId }}-toggle">
                        @foreach ($groups as $groupName => $children)
                            @if ($hasCustomGroups)
                                <div class="gw-dd-group">{{ $groupName }}</div>
                            @endif
                            @foreach ($children as $child)
                                <a href="{{ route('store.catalog', ['category' => $child->slug]) }}"
                                   class="{{ request('category') === $child->slug ? 'is-active' : '' }}">
                                    {{ $child->name }}
                                </a>
                            @endforeach
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            @php
                $defaultMenus = [
                    ['LIVING ROOM', route('store.catalog', ['q' => 'Sofa']), [
                        ['Sofas', route('store.catalog', ['q' => 'Sofa'])],
                        ['Lounge & Accent Chairs', route('store.catalog', ['q' => 'Chair'])],
                        ['Coffee & Center Tables', route('store.catalog', ['q' => 'Table'])],
                    ]],
                    ['BEDROOM', route('store.catalog', ['q' => 'Bed']), [
                        ['Beds', route('store.catalog', ['q' => 'Bed'])],
                        ['Metal Bed Frames', route('store.catalog', ['q' => 'Bed'])],
                    ]],
                    ['DINING', route('store.catalog', ['q' => 'Dining']), [
                        ['Dining Tables', route('store.catalog', ['q' => 'Dining Table'])],
                        ['Dining Chairs', route('store.catalog', ['q' => 'Dining Chair'])],
                        ['Dining Sets', route('store.catalog', ['q' => 'Dining'])],
                    ]],
                    ['OFFICE', route('store.catalog', ['q' => 'Office']), [
                        ['Office Chairs', route('store.catalog', ['q' => 'Office Chair'])],
                        ['Workstations', route('store.catalog', ['q' => 'Workstation'])],
                        ['Conference Tables', route('store.catalog', ['q' => 'Conference Table'])],
                    ]],
                    ['OUTDOOR', route('store.catalog', ['q' => 'Outdoor']), [
                        ['Outdoor Chairs', route('store.catalog', ['q' => 'Outdoor Chair'])],
                        ['Garden Furniture', route('store.catalog', ['q' => 'Outdoor'])],
                    ]],
                ];
            @endphp
            @foreach ($defaultMenus as [$label, $href, $subs])
                <div class="gw-menu-item">
                    <div class="gw-menu-trigger">
                        <a href="{{ $href }}">{{ $label }}</a>
                        <button type="button"
                                class="gw-caret-btn"
                                aria-expanded="false"
                                aria-label="Open {{ $label }} menu"
                                onclick="var item=this.closest('.gw-menu-item');var open=item.classList.toggle('dd-open');this.setAttribute('aria-expanded',open?'true':'false')">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                    <div class="gw-dd">
                        @foreach ($subs as [$subLabel, $subHref])
                            <a href="{{ $subHref }}">{{ $subLabel }}</a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endforelse

        <div class="gw-menu-item">
            <div class="gw-menu-trigger">
                <a href="{{ route('store.catalog', ['q' => 'Commercial']) }}">BUSINESS &amp; COMMERCIAL</a>
                <button type="button"
                        class="gw-caret-btn"
                        aria-expanded="false"
                        aria-label="Open Business & Commercial menu"
                        onclick="var item=this.closest('.gw-menu-item');var open=item.classList.toggle('dd-open');this.setAttribute('aria-expanded',open?'true':'false')">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            <div class="gw-dd">
                <a href="{{ route('store.catalog', ['q' => 'Hospitality']) }}">Hospitality</a>
                <a href="{{ route('store.catalog', ['q' => 'Dining']) }}">Restaurants &amp; Cafés</a>
                <a href="{{ route('store.catalog', ['q' => 'Office']) }}">Offices</a>
                <a href="https://wa.me/917418759171?text=GODWIN%20Bulk%20Order%20Enquiry" target="_blank" rel="noopener">Bulk Orders</a>
            </div>
        </div>

        <div class="gw-menu-item">
            <div class="gw-menu-trigger">
                <a href="{{ route('store.catalog') }}">FURNITURE ON RENT</a>
                <button type="button"
                        class="gw-caret-btn"
                        aria-expanded="false"
                        aria-label="Open Furniture on Rent menu"
                        onclick="var item=this.closest('.gw-menu-item');var open=item.classList.toggle('dd-open');this.setAttribute('aria-expanded',open?'true':'false')">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            <div class="gw-dd">
                <a href="{{ route('store.catalog', ['q' => 'Sofa']) }}">Home</a>
                <a href="{{ route('store.catalog', ['q' => 'Office']) }}">Office</a>
                <a href="{{ route('store.catalog', ['q' => 'Chair']) }}">Event</a>
                <a href="{{ route('store.catalog', ['q' => 'Dining']) }}">Commercial</a>
            </div>
        </div>

        <div class="gw-menu-item">
            <a href="{{ route('store.catalog') }}" class="{{ request()->routeIs('store.catalog') && ! request('category') ? 'is-active' : '' }}">COLLECTIONS</a>
        </div>
    </div>

    <form action="{{ route('store.catalog') }}" method="GET" class="gw-search-form m-0">
        <input type="text"
               name="q"
               value="{{ request('q') }}"
               placeholder="Search name, type or SKU"
               aria-label="Search">
    </form>

    <div class="gw-tools">
        @auth
            <a href="{{ route('store.account') }}">{{ \Illuminate\Support\Str::limit(auth()->user()->name, 12) }}</a>
            <form method="POST" action="{{ route('store.logout') }}" class="d-inline m-0">
                @csrf
                <button type="submit" class="gw-tool-btn">Sign out</button>
            </form>
        @else
            <a href="{{ route('store.login') }}">Account</a>
        @endauth

        <a href="{{ route('store.catalog') }}" title="Wishlist">♡ 0</a>

        <a href="{{ route('store.cart') }}" title="Cart">Cart {{ $cartCount ?? 0 }}</a>
    </div>
</nav>

<script>
    (function () {
        if (!window.__gwAnnInit) {
            window.__gwAnnInit = true;
            var annList = [
                'Discover the new era of metal furniture.',
                'Premium designs. Precision manufacturing.',
                'Explore our latest collections.'
            ];
            var ai = 2;
            setInterval(function () {
                var el = document.getElementById('gwAnnBar');
                if (!el) return;
                ai = (ai + 1) % annList.length;
                el.textContent = annList[ai];
            }, 4000);
        }

        if (!window.__gwMobileNavInit) {
            window.__gwMobileNavInit = true;
            var toggle = document.getElementById('gwMenuToggle');
            var menu = document.getElementById('gwMenuBar');
            var nav = document.getElementById('gwNav');
            if (toggle && menu) {
                var setMenuOpen = function (isOpen) {
                    menu.classList.toggle('open', isOpen);
                    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                    var icon = toggle.querySelector('i');
                    if (icon) {
                        icon.className = isOpen ? 'fas fa-times' : 'fas fa-bars';
                    }
                };

                toggle.addEventListener('click', function (e) {
                    e.stopPropagation();
                    setMenuOpen(!menu.classList.contains('open'));
                });

                document.addEventListener('click', function (e) {
                    if (menu.classList.contains('open') && nav && !nav.contains(e.target)) {
                        setMenuOpen(false);
                    }
                });

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape' && menu.classList.contains('open')) {
                        setMenuOpen(false);
                    }
                });
            }
        }
    })();
</script>
