@extends('layouts.store')

@section('title', 'Godwin Furniture')
@section('body_class', '')

@push('scripts')
    <script>
        function toggleHotspot(id) {
            var card = document.getElementById(id);
            if (card) {
                document.querySelectorAll('.hotspot-popover-card').forEach(function(c) {
                    if (c.id !== id) c.classList.remove('active');
                });
                card.classList.toggle('active');
            }
        }
        function closeHotspot(id) {
            var card = document.getElementById(id);
            if (card) {
                card.classList.remove('active');
            }
        }
        function toggleDesignerDrawer() {
            var drawer = document.getElementById('designerDrawer');
            if (drawer) {
                drawer.classList.toggle('active');
            }
        }
        function slideProductCarousel(carouselId, direction, event) {
            if (event) { event.preventDefault(); event.stopPropagation(); }
            var elem = document.getElementById(carouselId);
            if (!elem || typeof bootstrap === 'undefined') return;
            var carousel = bootstrap.Carousel.getOrCreateInstance(elem, { interval: false });
            direction === 'prev' ? carousel.prev() : carousel.next();
        }
        function goToProductSlide(carouselId, index, event) {
            if (event) { event.preventDefault(); event.stopPropagation(); }
            var elem = document.getElementById(carouselId);
            if (!elem || typeof bootstrap === 'undefined') return;
            bootstrap.Carousel.getOrCreateInstance(elem, { interval: false }).to(index);
        }
        function scrollBestSellers(direction) {
            scrollCarouselTrack('bestSellersTrack', direction);
        }
        function scrollCarouselTrack(trackId, direction) {
            var track = document.getElementById(trackId);
            if (!track) return;
            var card = track.querySelector('.bs-card-item');
            var step = card ? (card.offsetWidth + 20) * 2 : 640;
            track.scrollBy({ left: direction === 'prev' ? -step : step, behavior: 'smooth' });
        }
    </script>
@endpush
@section('content')
<!-- 1. Top Announcement Ticker Bar -->
    <div class="top-ticker-bar d-none d-lg-block">
        <div class="container-fluid px-5 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-4">
                <span>✨ Godwin Groups: Free White-Glove In-Home Delivery & Installation on Orders Over ₹49,999</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="#"><i class="fas fa-map-marker-alt me-1 opacity-75"></i> Store Locator</a>
                <span class="opacity-25">|</span>
                <a href="#"><i class="fas fa-mobile-alt me-1 opacity-75"></i> Download Our Apps</a>
                <span class="opacity-25">|</span>
                <a href="{{ auth()->check() ? route('store.orders.index') : route('store.login') }}"><i class="fas fa-truck me-1 opacity-75"></i> Track Furniture Order</a>
                <span class="opacity-25">|</span>
                <a href="https://wa.me/917418759171" target="_blank"><i class="fas fa-headset me-1 opacity-75"></i> Help</a>
            </div>
        </div>
    </div>

    <!-- 2. Main Glassmorphic Header (Scrolls naturally) -->
    <header class="header-luxury py-3">
        <div class="container-fluid px-4 px-lg-5">
            <div class="row align-items-center gy-2">
                
                <!-- Left: Hamburger (Mobile) & Brand Logo -->
                <div class="col-12 col-md-3 col-lg-3 d-flex align-items-center justify-content-between justify-content-md-start gap-3">
                    <button class="btn d-lg-none border-0 shadow-none p-0 text-dark fs-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <a href="{{ route('store.home') }}" class="text-decoration-none d-flex align-items-center">
                        <img src="{{ asset('store/images/logo.png') }}" alt="Godwin Groups Manufacturing" style="height: 48px; max-width: 220px; object-fit: contain;">
                    </a>

                    <!-- Mobile Right Cart Badge -->
                    <div class="d-flex d-lg-none align-items-center gap-3">
                        <a href="{{ route('store.cart') }}" class="text-dark position-relative fs-5"><i class="fas fa-shopping-bag"></i>@if (($cartCount ?? 0) > 0)<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 9px;">{{ $cartCount }}</span>@endif</a>
                    </div>
                </div>

                <!-- Middle: Search Input (Unified Compact Pill Search Bar) -->
                <div class="col-12 col-md-4 col-lg-4 d-none d-md-flex justify-content-center">
                    <form class="w-100 d-flex justify-content-center" action="{{ route('store.catalog') }}" method="GET">
                        <div class="search-box-wrapper">
                            <input type="text" name="q" class="form-control search-input-reduced text-dark shadow-none" placeholder="Search Metal Beds, Sofas...">
                            <button type="submit" class="search-btn-inside" title="Search">
                                <i class="fas fa-search fs-6"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Mobile Search Bar -->
                <div class="col-12 d-md-none mt-2">
                    <form action="{{ route('store.catalog') }}" method="GET">
                        <div class="search-box-wrapper" style="max-width: 100%;">
                            <input type="text" name="q" class="form-control search-input-reduced w-100 text-dark shadow-none" placeholder="Search furniture...">
                            <button type="submit" class="search-btn-inside" title="Search">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right: Utility Icons (Favourites, Account, Basket, More) -->
                <div class="col-12 col-md-5 col-lg-5 d-none d-lg-flex align-items-center justify-content-end gap-3 gap-xl-4">
                    
                    <!-- Favourites / Wishlist Icon -->
                    <a href="#" class="header-icon-item icon-favourites d-flex flex-column align-items-center justify-content-center">
                        <i class="far fa-heart mb-1"></i>
                        <span class="small-text fw-medium">Favourites</span>
                    </a>

                    <!-- Profile Account Dropdown -->
                    <div class="dropdown">
                        <a href="#" class="header-icon-item icon-account d-flex flex-column align-items-center justify-content-center" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="far fa-user mb-1"></i>
                            <span class="small-text fw-medium">{{ auth()->check() ? \Illuminate\Support\Str::limit(auth()->user()->name, 12) : 'Account' }}</span>
                        </a>
                        <div class="dropdown-menu shadow-lg border-0 rounded-3 p-3 mt-2" style="width: 260px; right: 0; left: auto;">
                            @auth
                                <div class="p-2 text-center border-bottom mb-2">
                                    <div class="fw-semibold font-heading text-dark mb-1">{{ auth()->user()->name }}</div>
                                    <div class="small text-muted mb-2">{{ auth()->user()->email }}</div>
                                    <form method="POST" action="{{ route('store.logout') }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-secondary w-100 font-heading fw-semibold">Sign Out</button>
                                    </form>
                                </div>
                                <a href="{{ route('store.account') }}" class="dropdown-item py-2 dropdown-item-custom"><i class="far fa-id-card me-2 opacity-75"></i> My Account</a>
                                <a href="{{ route('store.orders.index') }}" class="dropdown-item py-2 dropdown-item-custom"><i class="fas fa-box-open me-2 opacity-75"></i> My Orders</a>
                            @else
                                <div class="p-2 text-center border-bottom mb-2">
                                    <a href="{{ route('store.login') }}" class="btn btn-primary-luxury w-100 mb-2 text-decoration-none text-center d-block">SIGN IN</a>
                                    <span class="small text-muted">New Client? <a href="{{ route('store.register') }}" class="text-amber fw-semibold">Register Here</a></span>
                                </div>
                            @endauth
                            <a href="{{ route('store.cart') }}" class="dropdown-item py-2 dropdown-item-custom"><i class="fas fa-shopping-bag me-2 opacity-75"></i> My Bag</a>
                        </div>
                    </div>

                    <!-- Basket / Shopping Bag Button -->
                    <a href="{{ route('store.cart') }}" class="header-icon-item icon-basket d-flex flex-column align-items-center justify-content-center">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="fas fa-shopping-bag"></i>
                            @if (($cartCount ?? 0) > 0)
                                <span class="basket-badge-count">{{ $cartCount }}</span>
                            @endif
                        </div>
                        <span class="small-text fw-medium">Basket</span>
                    </a>

                    <!-- More Button / Icon Dropdown -->
                    <div class="dropdown">
                        <a href="#" class="header-icon-item icon-more d-flex flex-column align-items-center justify-content-center" data-bs-toggle="dropdown" aria-expanded="false" id="headerMoreDropdown">
                            <i class="fas fa-ellipsis-v mb-1"></i>
                            <span class="small-text fw-semibold">More</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-more border-0 mt-2" aria-labelledby="headerMoreDropdown">
                            <a class="dropdown-item more-item" href="#">Online Gift Card</a>
                            <a class="dropdown-item more-item" href="#">Bulk orders</a>
                            <a class="dropdown-item more-item" href="#">Corporate Gifts</a>
                            <a class="dropdown-item more-item" href="#">Offline Gift Card</a>
                            <a class="dropdown-item more-item item-highlighted" href="#">Homecentre Delight</a>
                            <a class="dropdown-item more-item" href="{{ route('store.catalog') }}">Catalogues</a>
                            <a class="dropdown-item more-item" href="#">Blog</a>
                            <a class="dropdown-item more-item" href="#">Store Locator</a>
                            <a class="dropdown-item more-item" href="#">Landmark Rewards SBI Credit card</a>
                            <a class="dropdown-item more-item" href="#">Furniture Exchange</a>
                            <a class="dropdown-item more-item" href="#">Terms and condition</a>
                            <a class="dropdown-item more-item" href="#">Landmark Group Foundation</a>
                            <a class="dropdown-item more-item item-accent" href="#">Gift Card Balance Check</a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </header>

    @include('store.partials.mega-nav')

    <!-- Mobile Story Ring Categories (Instagram / App Style) -->
    <div class="d-lg-none mt-2">
        <div class="mobile-story-container">
            @foreach ($storeMenu ?? [] as $room)
                <a href="{{ route('store.catalog', ['category' => $room->slug]) }}" class="mobile-story-item">
                    <div class="mobile-story-ring">
                        <img src="{{ $room->image_url ?: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=200' }}" class="mobile-story-img" alt="{{ $room->name }}">
                    </div>
                    <span class="mobile-story-label">{{ $room->name }}</span>
                </a>
            @endforeach
        </div>

        <!-- Mobile Quick Chips Filter Bar -->
        <div class="mobile-chips-bar">
            <a href="{{ route('store.catalog') }}" class="mobile-chip {{ request()->routeIs('store.catalog') && ! request('category') ? 'active' : '' }}">🔥 All</a>
            @foreach ($storeMenu ?? [] as $room)
                <a href="{{ route('store.catalog', ['category' => $room->slug]) }}" class="mobile-chip {{ request('category') === $room->slug ? 'active' : '' }}">{{ $room->name }}</a>
            @endforeach
        </div>
    </div>

    <!-- 3. Editorial Full-Width Hero Section -->
    <section class="container-fluid px-4 px-lg-5 mt-3 mt-lg-4">
        <div id="mainHeroCarousel" class="carousel slide carousel-fade hero-luxury-wrapper w-100" data-bs-ride="carousel" data-bs-interval="5500">
            <!-- Custom Bottom Controls & Indicators -->
            <div class="hero-controls-bar d-none d-sm-flex align-items-center justify-content-between">
                <div class="carousel-indicators hero-pill-indicators m-0 position-static">
                    <button type="button" data-bs-target="#mainHeroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#mainHeroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                    <button type="button" data-bs-target="#mainHeroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button class="hero-nav-btn" type="button" data-bs-target="#mainHeroCarousel" data-bs-slide="prev" aria-label="Previous slide">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <button class="hero-nav-btn" type="button" data-bs-target="#mainHeroCarousel" data-bs-slide="next" aria-label="Next slide">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <div class="carousel-inner h-100">
                <!-- Slide 1 -->
                <div class="carousel-item active hero-slide-item">
                    <img src="https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&q=80&w=1800&h=800" class="hero-slide-img" alt="Solid Wood & Metal Living">
                    <div class="hero-overlay-gradient"></div>

                    <!-- Top Floating Price / Highlight Tag -->
                    <div class="hero-floating-tag d-none d-md-flex align-items-center gap-2">
                        <span class="hero-tag-dot"></span>
                        <span>Factory Direct Pricing • <strong>Save up to 40%</strong></span>
                    </div>

                    <div class="hero-caption-content">
                        <span class="glass-hero-badge"><i class="fas fa-industry me-1.5 text-warning"></i> GODWIN MANUFACTURING EDITION</span>
                        <h1 class="font-serif">Artisanal Metal &amp; Solid Wood Furniture For Timeless Homes</h1>
                        <p>Engineered from 100% heavy-gauge CRCA steel &amp; seasoned Grade-A Teak wood with a 10-year structural guarantee.</p>
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <a href="{{ route('store.catalog') }}" class="btn btn-primary-luxury hero-cta-primary">
                                <span>Explore Factory Collection</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                            <a href="{{ route('store.catalog', ['sort' => 'newest']) }}" class="btn hero-cta-glass">
                                <i class="fas fa-compass me-1.5"></i> New Arrivals
                            </a>
                        </div>
                        <div class="hero-trust-strip d-none d-md-flex align-items-center gap-4 mt-4 pt-3">
                            <span><i class="fas fa-shield-alt text-warning me-1.5"></i> 10-Yr Structural Warranty</span>
                            <span><i class="fas fa-truck text-warning me-1.5"></i> Free White-Glove Setup</span>
                            <span><i class="fas fa-layer-group text-warning me-1.5"></i> Custom Finishes</span>
                        </div>
                    </div>
                </div>

                <!-- Slide 2 -->
                <div class="carousel-item hero-slide-item">
                    <img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=1800&h=800" class="hero-slide-img" alt="Chesterfield Sofas">
                    <div class="hero-overlay-gradient"></div>

                    <div class="hero-floating-tag d-none d-md-flex align-items-center gap-2">
                        <span class="hero-tag-dot"></span>
                        <span>Bespoke Upholstery • <strong>15+ Velvet &amp; Leather Shades</strong></span>
                    </div>

                    <div class="hero-caption-content">
                        <span class="glass-hero-badge"><i class="fas fa-couch me-1.5 text-warning"></i> LUXURY LIVING SEATING</span>
                        <h1 class="font-serif">Bespoke Italian Leather &amp; Reinforced Steel Frame Sofas</h1>
                        <p>Unrivaled lounge comfort crafted with high-density orthopedic foam and powder-coated structural steel frames.</p>
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <a href="{{ route('store.catalog') }}" class="btn btn-primary-luxury hero-cta-primary">
                                <span>Discover Living Room</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                            <a href="{{ route('store.catalog', ['sort' => 'popular']) }}" class="btn hero-cta-glass">
                                View Bestsellers
                            </a>
                        </div>
                        <div class="hero-trust-strip d-none d-md-flex align-items-center gap-4 mt-4 pt-3">
                            <span><i class="fas fa-check-circle text-warning me-1.5"></i> 32 kg/m³ High-Density Foam</span>
                            <span><i class="fas fa-check-circle text-warning me-1.5"></i> Stain-Resistant Fabrics</span>
                        </div>
                    </div>
                </div>

                <!-- Slide 3 -->
                <div class="carousel-item hero-slide-item">
                    <img src="https://images.unsplash.com/photo-1505693314120-0d443867891c?auto=format&fit=crop&q=80&w=1800&h=800" class="hero-slide-img" alt="Bedroom Suites">
                    <div class="hero-overlay-gradient"></div>

                    <div class="hero-floating-tag d-none d-md-flex align-items-center gap-2">
                        <span class="hero-tag-dot"></span>
                        <span>Heavy-Duty Bedroom Suites • <strong>Hydraulic Storage</strong></span>
                    </div>

                    <div class="hero-caption-content">
                        <span class="glass-hero-badge"><i class="fas fa-bed me-1.5 text-warning"></i> SIGNATURE BEDROOM SUITES</span>
                        <h1 class="font-serif">King &amp; Queen Solid Teak Beds With Steel Core</h1>
                        <p>Engineered for zero-creak stability with hydraulic under-bed storage and matching modular wardrobes.</p>
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <a href="{{ route('store.catalog') }}" class="btn btn-primary-luxury hero-cta-primary">
                                <span>Shop Bedroom Collection</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                        <div class="hero-trust-strip d-none d-md-flex align-items-center gap-4 mt-4 pt-3">
                            <span><i class="fas fa-check-circle text-warning me-1.5"></i> Zero-Creak Steel Support</span>
                            <span><i class="fas fa-check-circle text-warning me-1.5"></i> Termite-Resistant Teak</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. Editorial Spaces Showcase -->
    @php
        $roomModels = collect($storeMenu ?? [])->keyBy('name');

        $spacesShowcase = [
            [
                'number' => '01',
                'eyebrow' => 'LIVING & LOUNGE',
                'name' => 'Living Room',
                'description' => 'Sofas, lounge chairs and tables built on slim, strong metal frames.',
                'room' => $roomModels->get('Living Room'),
                'fallback_tags' => ['Sofas', 'Lounge Chairs', 'Coffee Tables', 'TV Units'],
                'image' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&q=80&w=1200',
                'reverse' => false,
            ],
            [
                'number' => '02',
                'eyebrow' => 'REST & SANCTUARY',
                'name' => 'Bedroom',
                'description' => 'Metal bed frames and storage with a quiet, warm finish.',
                'room' => $roomModels->get('Bedroom'),
                'fallback_tags' => ['King Size Beds', 'Queen Size Beds', 'Wardrobes', 'Nightstands'],
                'image' => 'https://images.unsplash.com/photo-1505693314120-0d443867891c?auto=format&fit=crop&q=80&w=1200',
                'reverse' => true,
            ],
            [
                'number' => '03',
                'eyebrow' => 'GATHER & HOST',
                'name' => 'Dining Room',
                'description' => 'Tables and chairs made for long dinners and daily use.',
                'room' => $roomModels->get('Dining Room'),
                'fallback_tags' => ['Dining Tables', 'Dining Chairs', 'Bar Stools', 'Sideboards'],
                'image' => 'https://images.unsplash.com/photo-1617806118233-18e1de247200?auto=format&fit=crop&q=80&w=1200',
                'reverse' => false,
            ],
            [
                'number' => '04',
                'eyebrow' => 'EXECUTIVE WORKSPACE',
                'name' => 'Office',
                'description' => 'Desks, chairs and workstations for focused work.',
                'room' => $roomModels->get('Office'),
                'fallback_tags' => ['Executive Chairs', 'Office Desks', 'Workstations', 'Filing Cabinets'],
                'image' => 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&q=80&w=1200',
                'reverse' => true,
            ],
        ];
    @endphp

    <section class="container-fluid px-4 px-lg-5 my-5 pt-2">
        <div class="spaces-editorial-intro text-center">
            <span class="spaces-intro-kicker">CURATED LIVING SPACES</span>
            <h2 class="spaces-intro-title">Spaces that define your lifestyle.</h2>
            <p class="spaces-intro-subtitle">Discover thoughtfully designed furniture for every corner of your world.</p>
            <div class="spaces-intro-accent"></div>
        </div>

        <div class="spaces-editorial-stack">
            @foreach ($spacesShowcase as $space)
                @php
                    /** @var \App\Models\Category|null $roomObj */
                    $roomObj = $space['room'];
                    $slug = $roomObj?->slug;
                    $exploreUrl = $slug
                        ? route('store.catalog', ['category' => $slug])
                        : route('store.catalog', ['q' => $space['name']]);
                    $shopUrl = $slug
                        ? route('store.catalog', ['category' => $slug, 'sort' => 'popular'])
                        : route('store.catalog', ['q' => $space['name'], 'sort' => 'popular']);
                    $quickChildren = $roomObj?->children?->take(4) ?? collect();
                @endphp
                <div class="space-editorial-row {{ $space['reverse'] ? 'is-reversed' : '' }}">
                    <div class="space-editorial-copy">
                        <div class="space-copy-inner">
                            <div class="space-meta-header">
                                <span class="space-index-num">{{ $space['number'] }}</span>
                                <span class="space-meta-line"></span>
                                <span class="space-eyebrow">{{ $space['eyebrow'] }}</span>
                            </div>

                            <h3 class="space-editorial-heading">{{ $space['name'] }}</h3>
                            <p class="space-editorial-desc">{{ $space['description'] }}</p>

                            <div class="space-quick-pills">
                                @if ($quickChildren->isNotEmpty())
                                    @foreach ($quickChildren as $child)
                                        <a href="{{ route('store.catalog', ['category' => $child->slug]) }}" class="space-quick-pill">{{ $child->name }}</a>
                                    @endforeach
                                @else
                                    @foreach ($space['fallback_tags'] as $tag)
                                        <a href="{{ $exploreUrl }}" class="space-quick-pill">{{ $tag }}</a>
                                    @endforeach
                                @endif
                            </div>

                            <div class="space-editorial-actions">
                                <a href="{{ $exploreUrl }}" class="btn-space-outline">
                                    <span>Explore room</span>
                                </a>
                                <a href="{{ $shopUrl }}" class="btn-space-solid">
                                    <span>Shop this space</span>
                                    <i class="fas fa-arrow-right ms-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="space-editorial-media">
                        <a href="{{ $exploreUrl }}" class="d-block w-100 h-100">
                            <img src="{{ $space['image'] }}" alt="{{ $space['name'] }}" class="space-editorial-img" loading="lazy">
                            <div class="space-media-badge">
                                <span><i class="fas fa-shield-alt text-warning me-1.5"></i> CRCA Steel &amp; Grade-A Teak</span>
                            </div>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- 4B. Shop by Category Section -->
    @php
        $allLeafSlugs = collect($storeMenu ?? [])
            ->flatMap(fn ($room) => $room->children)
            ->pluck('slug', 'name');

        $shopByCategories = [
            [
                'label' => 'Sofas',
                'subtitle' => '3-Seater, Sectional & Chester',
                'slug' => $allLeafSlugs['Sofa'] ?? ($roomModels->get('Living Room')?->slug),
                'query' => 'Sofa',
                'image' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=700',
            ],
            [
                'label' => 'Chairs',
                'subtitle' => 'Accent, Lounge & Recliners',
                'slug' => $allLeafSlugs['Accent Chair'] ?? $allLeafSlugs['Lounge Chair'] ?? null,
                'query' => 'Chair',
                'image' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&q=80&w=700',
            ],
            [
                'label' => 'Tables',
                'subtitle' => 'Coffee, Centre & Console',
                'slug' => $allLeafSlugs['Coffee Table'] ?? $allLeafSlugs['Centre Table'] ?? null,
                'query' => 'Table',
                'image' => 'https://images.unsplash.com/photo-1532372576444-dda954194ad0?auto=format&fit=crop&q=80&w=700',
            ],
            [
                'label' => 'Beds',
                'subtitle' => 'King, Queen & Hydraulic Steel',
                'slug' => $allLeafSlugs['King Size Bed'] ?? $allLeafSlugs['Queen Size Bed'] ?? ($roomModels->get('Bedroom')?->slug),
                'query' => 'Bed',
                'image' => 'https://images.unsplash.com/photo-1505693314120-0d443867891c?auto=format&fit=crop&q=80&w=700',
            ],
            [
                'label' => 'Dining',
                'subtitle' => '6 & 8-Seater Dining Sets',
                'slug' => $roomModels->get('Dining Room')?->slug ?? $allLeafSlugs['Dining Table'] ?? null,
                'query' => 'Dining',
                'image' => 'https://images.unsplash.com/photo-1617806118233-18e1de247200?auto=format&fit=crop&q=80&w=700',
            ],
            [
                'label' => 'Office',
                'subtitle' => 'Executive Desks & Ergonomic',
                'slug' => $roomModels->get('Office')?->slug ?? $allLeafSlugs['Office Desk'] ?? null,
                'query' => 'Office',
                'image' => 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&q=80&w=700',
            ],
        ];
    @endphp

    <section class="container-fluid px-4 px-lg-5 my-5 pt-2">
        <div class="d-flex flex-column flex-sm-row align-items-sm-end justify-content-between mb-4 gap-2">
            <div>
                <span class="shop-cat-kicker">BROWSE SILHOUETTES</span>
                <h2 class="shop-cat-heading m-0">Shop by category</h2>
            </div>
            <a href="{{ route('store.catalog') }}" class="shop-cat-viewall">
                <span>View Full Catalog</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="row g-3 g-lg-4">
            @foreach ($shopByCategories as $cat)
                @php
                    $catUrl = $cat['slug']
                        ? route('store.catalog', ['category' => $cat['slug']])
                        : route('store.catalog', ['q' => $cat['query']]);
                @endphp
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="{{ $catUrl }}" class="shop-cat-card">
                        <div class="shop-cat-thumb">
                            <img src="{{ $cat['image'] }}" alt="{{ $cat['label'] }}" class="shop-cat-img" loading="lazy">
                            <span class="shop-cat-hover-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </span>
                        </div>
                        <div class="shop-cat-meta">
                            <h6 class="shop-cat-title">{{ $cat['label'] }}</h6>
                            <span class="shop-cat-sub">{{ $cat['subtitle'] }}</span>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </section>

    <!-- 5. Best Sellers, 6. New Arrivals & 7. Featured Pieces Carousels -->
    @php
        $ratingsCycle = ['4.6', '4.8', '4.5', '4.7', '4.9', '4.6', '4.8', '4.7'];

        $mapCarouselCards = function ($collection, string $badgeMode = 'new') use ($ratingsCycle): array {
            $cards = [];
            foreach (collect($collection)->values() as $idx => $prod) {
                $firstVariant = $prod->activeVariants->sortBy('price')->first() ?? $prod->activeVariants->first();
                $price = $firstVariant ? (float) $firstVariant->storePrice() : (float) $prod->fromPrice();
                $mrp = $firstVariant && $firstVariant->mrp() ? (float) $firstVariant->mrp() : null;
                $discount = ($mrp && $mrp > $price) ? (int) round((($mrp - $price) / $mrp) * 100) : null;
                $colourName = $firstVariant?->colour ?: 'Walnut';
                $colourHex = $firstVariant?->colour_hex ?: \App\Support\Catalog::colourHex($colourName);
                $image = $prod->primaryImageUrl() ?: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=700';

                $badge = match ($badgeMode) {
                    'always_new' => 'New',
                    'featured' => 'Featured',
                    default => ($idx % 2 === 0 ? 'New' : null),
                };

                $cards[] = [
                    'name' => $prod->name,
                    'price' => $price,
                    'mrp' => $mrp,
                    'discount' => $discount,
                    'rating' => $ratingsCycle[$idx % count($ratingsCycle)],
                    'colour' => $colourName,
                    'colour_hex' => $colourHex,
                    'subtitle' => $prod->material ?: ($prod->product_category ?: 'Factory direct'),
                    'badge' => $badge,
                    'image' => $image,
                    'url' => route('store.product', $prod->slug),
                    'variant_id' => $firstVariant?->id,
                ];
            }
            return $cards;
        };

        $bestSellerPool = collect($featuredProducts ?? [])->concat($newProducts ?? [])->unique('id')->take(8);
        $bestSellerCards = $mapCarouselCards($bestSellerPool, 'new');
        $newArrivalCards = $mapCarouselCards(collect($newProducts ?? [])->take(8), 'always_new');
        $featuredCards = $mapCarouselCards(collect($featuredProducts ?? [])->take(8), 'featured');

        if ($bestSellerCards === []) {
            $bestSellerCards = [
                ['name' => 'Arden 3-Seater Sofa', 'price' => 48900, 'mrp' => 58900, 'discount' => 17, 'rating' => '4.6', 'colour' => 'Grey', 'colour_hex' => '#8E9294', 'subtitle' => 'CRCA Steel & Velvet', 'badge' => 'New', 'image' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['q' => 'Sofa']), 'variant_id' => null],
                ['name' => 'Linea L-Shaped Sofa', 'price' => 72900, 'mrp' => null, 'discount' => null, 'rating' => '4.4', 'colour' => 'Beige', 'colour_hex' => '#D4C5AE', 'subtitle' => 'Modular Sectional', 'badge' => null, 'image' => 'https://images.unsplash.com/photo-1493663284031-b7e3aefcae8e?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['q' => 'Sofa']), 'variant_id' => null],
                ['name' => 'Kern Lounge Chair', 'price' => 18900, 'mrp' => 21900, 'discount' => 14, 'rating' => '4.5', 'colour' => 'Brown', 'colour_hex' => '#5C3D2A', 'subtitle' => 'Italian Leather', 'badge' => 'New', 'image' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['q' => 'Chair']), 'variant_id' => null],
                ['name' => 'Rolo Dining Chair', 'price' => 5900, 'mrp' => 7500, 'discount' => 21, 'rating' => '4.2', 'colour' => 'Black', 'colour_hex' => '#1F2428', 'subtitle' => 'Powder Coated Steel', 'badge' => null, 'image' => 'https://images.unsplash.com/photo-1592078615290-033ee584e267?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['q' => 'Dining Chair']), 'variant_id' => null],
                ['name' => 'Orla Coffee Table', 'price' => 12900, 'mrp' => 15900, 'discount' => 19, 'rating' => '4.5', 'colour' => 'Gold', 'colour_hex' => '#B38E50', 'subtitle' => 'Solid Teak & Steel', 'badge' => 'New', 'image' => 'https://images.unsplash.com/photo-1532372576444-dda954194ad0?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['q' => 'Table']), 'variant_id' => null],
                ['name' => 'Monarch King Size Bed', 'price' => 44900, 'mrp' => 54900, 'discount' => 18, 'rating' => '4.8', 'colour' => 'Walnut', 'colour_hex' => '#5C3D2A', 'subtitle' => 'Hydraulic Steel Core', 'badge' => null, 'image' => 'https://images.unsplash.com/photo-1505693314120-0d443867891c?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['q' => 'Bed']), 'variant_id' => null],
            ];
        }

        if ($newArrivalCards === []) {
            $newArrivalCards = [
                ['name' => 'Oslo Solid Teak Queen Bed', 'price' => 38500, 'mrp' => 46900, 'discount' => 18, 'rating' => '4.8', 'colour' => 'Natural oak', 'colour_hex' => '#C9A574', 'subtitle' => 'Solid Teak & Steel', 'badge' => 'New', 'image' => 'https://images.unsplash.com/photo-1505693314120-0d443867891c?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['sort' => 'newest']), 'variant_id' => null],
                ['name' => 'Milano 6-Seater Dining Table', 'price' => 54000, 'mrp' => 68000, 'discount' => 21, 'rating' => '4.9', 'colour' => 'Walnut', 'colour_hex' => '#5C3D2A', 'subtitle' => 'CRCA Heavy Steel', 'badge' => 'New', 'image' => 'https://images.unsplash.com/photo-1617806118233-18e1de247200?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['sort' => 'newest']), 'variant_id' => null],
                ['name' => 'Verona Velvet Accent Chair', 'price' => 14500, 'mrp' => 17900, 'discount' => 19, 'rating' => '4.7', 'colour' => 'Forest green', 'colour_hex' => '#2F5D4A', 'subtitle' => 'Velvet & Brass Steel', 'badge' => 'New', 'image' => 'https://images.unsplash.com/photo-1567538096630-e0c55bd6374c?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['sort' => 'newest']), 'variant_id' => null],
                ['name' => 'Metro Industrial TV Console', 'price' => 19200, 'mrp' => 24500, 'discount' => 22, 'rating' => '4.6', 'colour' => 'Charcoal', 'colour_hex' => '#3B4247', 'subtitle' => 'Powder Coated Steel', 'badge' => 'New', 'image' => 'https://images.unsplash.com/photo-1618220179428-22790b461013?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['sort' => 'newest']), 'variant_id' => null],
                ['name' => 'Windsor 3-Door Steel Wardrobe', 'price' => 36000, 'mrp' => 44900, 'discount' => 20, 'rating' => '4.8', 'colour' => 'Ivory', 'colour_hex' => '#E8E2D3', 'subtitle' => 'CRCA Steel & Teak', 'badge' => 'New', 'image' => 'https://images.unsplash.com/photo-1595428774223-ef52624120d2?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['sort' => 'newest']), 'variant_id' => null],
                ['name' => 'Apex Ergonomic Executive Desk', 'price' => 24900, 'mrp' => 31500, 'discount' => 21, 'rating' => '4.7', 'colour' => 'Walnut', 'colour_hex' => '#5C3D2A', 'subtitle' => 'Steel & Walnut Top', 'badge' => 'New', 'image' => 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['sort' => 'newest']), 'variant_id' => null],
            ];
        }

        if ($featuredCards === []) {
            $featuredCards = [
                ['name' => 'Alanis 3-Seater Velvet Sofa', 'price' => 42000, 'mrp' => 54999, 'discount' => 24, 'rating' => '4.9', 'colour' => 'Forest green', 'colour_hex' => '#2F5D4A', 'subtitle' => 'CRCA Steel & Velvet', 'badge' => 'Featured', 'image' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['sort' => 'popular']), 'variant_id' => null],
                ['name' => 'Imperial King Size Teak Bed', 'price' => 45000, 'mrp' => 59999, 'discount' => 25, 'rating' => '4.9', 'colour' => 'Charcoal', 'colour_hex' => '#3B4247', 'subtitle' => 'CRCA Steel & Teak', 'badge' => 'Featured', 'image' => 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['sort' => 'popular']), 'variant_id' => null],
                ['name' => 'Nordic Modular Sectional Sofa', 'price' => 78000, 'mrp' => 94999, 'discount' => 18, 'rating' => '4.8', 'colour' => 'Ash grey', 'colour_hex' => '#9AA1A3', 'subtitle' => 'Powder Coated Steel', 'badge' => 'Featured', 'image' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['sort' => 'popular']), 'variant_id' => null],
                ['name' => 'Imperial 6-Seater Dining Set', 'price' => 54000, 'mrp' => 69999, 'discount' => 23, 'rating' => '4.8', 'colour' => 'Walnut', 'colour_hex' => '#5C3D2A', 'subtitle' => 'CRCA Heavy Steel', 'badge' => 'Featured', 'image' => 'https://images.unsplash.com/photo-1577140917170-285929fb55b7?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['sort' => 'popular']), 'variant_id' => null],
                ['name' => 'Monaco Italian Leather Recliner', 'price' => 28500, 'mrp' => 36900, 'discount' => 23, 'rating' => '4.9', 'colour' => 'Tan leather', 'colour_hex' => '#A8693A', 'subtitle' => 'Full Grain Leather', 'badge' => 'Featured', 'image' => 'https://images.unsplash.com/photo-1580480055273-228ff5388ef8?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['sort' => 'popular']), 'variant_id' => null],
                ['name' => 'Florence Marble Top Coffee Table', 'price' => 16900, 'mrp' => 21900, 'discount' => 23, 'rating' => '4.7', 'colour' => 'Charcoal', 'colour_hex' => '#3B4247', 'subtitle' => 'Steel & Marble', 'badge' => 'Featured', 'image' => 'https://images.unsplash.com/photo-1532372576444-dda954194ad0?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['sort' => 'popular']), 'variant_id' => null],
            ];
        }

        $productCarousels = [
            [
                'kicker' => 'CUSTOMER FAVOURITES',
                'title' => 'Best sellers',
                'track_id' => 'bestSellersTrack',
                'view_all_url' => route('store.catalog', ['sort' => 'popular']),
                'view_all_label' => 'View all',
                'items' => $bestSellerCards,
            ],
            [
                'kicker' => 'FROM THE FACTORY FLOOR',
                'title' => 'New Arrivals',
                'track_id' => 'newArrivalsTrack',
                'view_all_url' => route('store.catalog', ['sort' => 'newest']),
                'view_all_label' => 'View all',
                'items' => $newArrivalCards,
            ],
            [
                'kicker' => 'CURATED SIGNATURE SUITES',
                'title' => 'Featured Pieces',
                'track_id' => 'featuredPiecesTrack',
                'view_all_url' => route('store.catalog', ['sort' => 'popular']),
                'view_all_label' => 'View all',
                'items' => $featuredCards,
            ],
        ];
    @endphp

    @foreach ($productCarousels as $carouselSection)
        <section class="container-fluid px-4 px-lg-5 my-5 pt-2">
            <div class="d-flex align-items-end justify-content-between mb-4">
                <div>
                    <span class="shop-cat-kicker">{{ $carouselSection['kicker'] }}</span>
                    <h2 class="bs-section-heading m-0">{{ $carouselSection['title'] }}</h2>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ $carouselSection['view_all_url'] }}" class="bs-view-all-link">{{ $carouselSection['view_all_label'] }}</a>
                    <div class="d-none d-sm-flex align-items-center gap-2">
                        <button type="button" class="bs-header-nav-btn" onclick="scrollCarouselTrack('{{ $carouselSection['track_id'] }}', 'prev')" aria-label="Previous {{ $carouselSection['title'] }}">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button type="button" class="bs-header-nav-btn" onclick="scrollCarouselTrack('{{ $carouselSection['track_id'] }}', 'next')" aria-label="Next {{ $carouselSection['title'] }}">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="bs-carousel-track" id="{{ $carouselSection['track_id'] }}">
                @foreach ($carouselSection['items'] as $item)
                    <div class="bs-card-item">
                        <div class="bs-product-card">
                            <div class="bs-card-media">
                                @if (! empty($item['badge']))
                                    <span class="bs-badge-new {{ $item['badge'] === 'Featured' ? 'bs-badge-featured' : '' }}">{{ $item['badge'] }}</span>
                                @endif
                                <button type="button" class="bs-btn-wishlist" aria-label="Add {{ $item['name'] }} to wishlist" onclick="this.classList.toggle('is-liked')">
                                    <i class="far fa-heart"></i>
                                </button>
                                <a href="{{ $item['url'] }}" class="d-block w-100 h-100">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="bs-card-img" loading="lazy">
                                </a>
                            </div>

                            <div class="bs-card-body">
                                <a href="{{ $item['url'] }}" class="text-decoration-none">
                                    <h5 class="bs-card-title" title="{{ $item['name'] }}">{{ $item['name'] }}</h5>
                                </a>

                                <div class="bs-price-row">
                                    <span class="bs-price-current">₹{{ number_format($item['price'], 0) }}</span>
                                    @if ($item['mrp'] && $item['mrp'] > $item['price'])
                                        <span class="bs-price-mrp">₹{{ number_format($item['mrp'], 0) }}</span>
                                    @endif
                                    @if ($item['discount'])
                                        <span class="bs-price-off">{{ $item['discount'] }}% off</span>
                                    @endif
                                </div>

                                <div class="bs-meta-row">
                                    <span class="bs-rating"><i class="fas fa-star me-1"></i>{{ $item['rating'] }}</span>
                                    <span class="bs-meta-dot">·</span>
                                    <span class="bs-swatch-dot" style="background-color: {{ $item['colour_hex'] }};"></span>
                                    <span class="bs-colour-name">{{ $item['colour'] }}</span>
                                    <span class="bs-meta-dot">·</span>
                                    <span class="bs-meta-sub text-truncate">{{ $item['subtitle'] }}</span>
                                </div>

                                <div class="bs-card-actions">
                                    @if ($item['variant_id'])
                                        <form method="POST" action="{{ route('store.cart.add') }}" class="flex-grow-1 m-0">
                                            @csrf
                                            <input type="hidden" name="product_variant_id" value="{{ $item['variant_id'] }}">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="bs-btn-add w-100">Add to cart</button>
                                        </form>
                                    @else
                                        <a href="{{ $item['url'] }}" class="bs-btn-add flex-grow-1">Add to cart</a>
                                    @endif
                                    <a href="{{ $item['url'] }}" class="bs-btn-quick">Quick view</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach
    <!-- 8. Category Wise Products Showcase -->
    <section class="container-fluid px-4 px-lg-5 my-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
            <div class="section-title-editorial m-0">
                <span class="subtitle">FACTORY PRODUCED SUITES</span>
                <h2>Category Wise Products Showcase</h2>
            </div>
            <!-- Category Pills / Tabs Nav -->
            <ul class="nav nav-pills category-pills-nav font-heading fw-bold gap-2" id="categoryProductsTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill px-3 py-2" id="cat-sofas-tab" data-bs-toggle="tab" data-bs-target="#cat-sofas" type="button" role="tab">🛋️ Sofas</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-3 py-2" id="cat-beds-tab" data-bs-toggle="tab" data-bs-target="#cat-beds" type="button" role="tab">🛏️ Metal Beds</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-3 py-2" id="cat-dining-tab" data-bs-toggle="tab" data-bs-target="#cat-dining" type="button" role="tab">🍽️ Dining</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-3 py-2" id="cat-desks-tab" data-bs-toggle="tab" data-bs-target="#cat-desks" type="button" role="tab">💼 Desks</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-3 py-2" id="cat-almirahs-tab" data-bs-toggle="tab" data-bs-target="#cat-almirahs" type="button" role="tab">🗄️ Almirahs</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-3 py-2" id="cat-recliners-tab" data-bs-toggle="tab" data-bs-target="#cat-recliners" type="button" role="tab">🪑 Recliners</button>
                </li>
            </ul>
        </div>

        
        <div class="tab-content mt-3" id="categoryProductsTabContent">
                        <div class="tab-pane fade show active" id="cat-sofas" role="tabpanel">
                <div class="row g-4">
                                        <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-2.5 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="font-size: 10px;">
                                    GODWIN DIRECT
                                </span>
                                <span class="position-absolute top-0 end-0 m-2.5 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 4.9                                </span>
                                <a href="{{ route('store.catalog') }}">
                                    <img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block" style="height: 190px;" alt="Alanis Metal Frame 3-Seater Velvet Sofa">
                                </a>
                            </div>
                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">CRCA Steel & Velvet</span>
                                    <a href="{{ route('store.catalog') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-2 text-truncate" title="Alanis Metal Frame 3-Seater Velvet Sofa" style="font-size: 13px; line-height: 1.4;">Alanis Metal Frame 3-Seater Velvet Sofa</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark" style="font-size: 14px;">₹38,999</span>
                                        <span class="text-muted text-decoration-line-through small ms-1" style="font-size: 10px;">₹54,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-2.5 py-1 font-heading fw-semibold" style="font-size: 11px;"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                        <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-2.5 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="font-size: 10px;">
                                    GODWIN DIRECT
                                </span>
                                <span class="position-absolute top-0 end-0 m-2.5 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 4.8                                </span>
                                <a href="{{ route('store.catalog') }}">
                                    <img src="https://images.unsplash.com/photo-1493663284031-b7e3aefcae8e?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block" style="height: 190px;" alt="Chesterfield Italian Cognac Leather Sofa">
                                </a>
                            </div>
                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">Italian Full Grain Leather</span>
                                    <a href="{{ route('store.catalog') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-2 text-truncate" title="Chesterfield Italian Cognac Leather Sofa" style="font-size: 13px; line-height: 1.4;">Chesterfield Italian Cognac Leather Sofa</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark" style="font-size: 14px;">₹52,999</span>
                                        <span class="text-muted text-decoration-line-through small ms-1" style="font-size: 10px;">₹74,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-2.5 py-1 font-heading fw-semibold" style="font-size: 11px;"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                        <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-2.5 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="font-size: 10px;">
                                    GODWIN DIRECT
                                </span>
                                <span class="position-absolute top-0 end-0 m-2.5 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 4.9                                </span>
                                <a href="{{ route('store.catalog') }}">
                                    <img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block" style="height: 190px;" alt="Nordic Modular Sectional Steel Sofa">
                                </a>
                            </div>
                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">Powder Coated Frame</span>
                                    <a href="{{ route('store.catalog') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-2 text-truncate" title="Nordic Modular Sectional Steel Sofa" style="font-size: 13px; line-height: 1.4;">Nordic Modular Sectional Steel Sofa</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark" style="font-size: 14px;">₹45,999</span>
                                        <span class="text-muted text-decoration-line-through small ms-1" style="font-size: 10px;">₹62,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-2.5 py-1 font-heading fw-semibold" style="font-size: 11px;"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                        <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-2.5 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="font-size: 10px;">
                                    GODWIN DIRECT
                                </span>
                                <span class="position-absolute top-0 end-0 m-2.5 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 4.7                                </span>
                                <a href="{{ route('store.catalog') }}">
                                    <img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block" style="height: 190px;" alt="Royal Velvet Tufted Lounge Sofa">
                                </a>
                            </div>
                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">Gold Plated Steel Legs</span>
                                    <a href="{{ route('store.catalog') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-2 text-truncate" title="Royal Velvet Tufted Lounge Sofa" style="font-size: 13px; line-height: 1.4;">Royal Velvet Tufted Lounge Sofa</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark" style="font-size: 14px;">₹34,499</span>
                                        <span class="text-muted text-decoration-line-through small ms-1" style="font-size: 10px;">₹48,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-2.5 py-1 font-heading fw-semibold" style="font-size: 11px;"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                    </div>
            </div>
                        <div class="tab-pane fade " id="cat-beds" role="tabpanel">
                <div class="row g-4">
                                        <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-2.5 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="font-size: 10px;">
                                    GODWIN DIRECT
                                </span>
                                <span class="position-absolute top-0 end-0 m-2.5 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 4.9                                </span>
                                <a href="{{ route('store.catalog') }}">
                                    <img src="https://images.unsplash.com/photo-1505693314120-0d443867891c?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block" style="height: 190px;" alt="Godwin Heavy Metal & Teak King Bed">
                                </a>
                            </div>
                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">Steel & Teak Wood</span>
                                    <a href="{{ route('store.catalog') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-2 text-truncate" title="Godwin Heavy Metal & Teak King Bed" style="font-size: 13px; line-height: 1.4;">Godwin Heavy Metal & Teak King Bed</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark" style="font-size: 14px;">₹44,499</span>
                                        <span class="text-muted text-decoration-line-through small ms-1" style="font-size: 10px;">₹59,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-2.5 py-1 font-heading fw-semibold" style="font-size: 11px;"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                        <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-2.5 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="font-size: 10px;">
                                    GODWIN DIRECT
                                </span>
                                <span class="position-absolute top-0 end-0 m-2.5 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 5                                </span>
                                <a href="{{ route('store.catalog') }}">
                                    <img src="https://images.unsplash.com/photo-1540518614846-7ede433c5163?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block" style="height: 190px;" alt="Godwin Hydraulic Storage Steel Queen Bed">
                                </a>
                            </div>
                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">Heavy Hydraulic Lift</span>
                                    <a href="{{ route('store.catalog') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-2 text-truncate" title="Godwin Hydraulic Storage Steel Queen Bed" style="font-size: 13px; line-height: 1.4;">Godwin Hydraulic Storage Steel Queen Bed</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark" style="font-size: 14px;">₹48,999</span>
                                        <span class="text-muted text-decoration-line-through small ms-1" style="font-size: 10px;">₹65,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-2.5 py-1 font-heading fw-semibold" style="font-size: 11px;"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                        <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-2.5 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="font-size: 10px;">
                                    GODWIN DIRECT
                                </span>
                                <span class="position-absolute top-0 end-0 m-2.5 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 4.8                                </span>
                                <a href="{{ route('store.catalog') }}">
                                    <img src="https://images.unsplash.com/photo-1595428774223-ef52624120d2?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block" style="height: 190px;" alt="Imperial Canopy Metal Bed Frame">
                                </a>
                            </div>
                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">Matte Black Steel</span>
                                    <a href="{{ route('store.catalog') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-2 text-truncate" title="Imperial Canopy Metal Bed Frame" style="font-size: 13px; line-height: 1.4;">Imperial Canopy Metal Bed Frame</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark" style="font-size: 14px;">₹39,999</span>
                                        <span class="text-muted text-decoration-line-through small ms-1" style="font-size: 10px;">₹54,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-2.5 py-1 font-heading fw-semibold" style="font-size: 11px;"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                        <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-2.5 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="font-size: 10px;">
                                    GODWIN DIRECT
                                </span>
                                <span class="position-absolute top-0 end-0 m-2.5 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 4.9                                </span>
                                <a href="{{ route('store.catalog') }}">
                                    <img src="https://images.unsplash.com/photo-1505693314120-0d443867891c?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block" style="height: 190px;" alt="Industrial Anti-Squeak Steel Bed">
                                </a>
                            </div>
                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">CRCA Heavy Gauge</span>
                                    <a href="{{ route('store.catalog') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-2 text-truncate" title="Industrial Anti-Squeak Steel Bed" style="font-size: 13px; line-height: 1.4;">Industrial Anti-Squeak Steel Bed</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark" style="font-size: 14px;">₹26,999</span>
                                        <span class="text-muted text-decoration-line-through small ms-1" style="font-size: 10px;">₹36,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-2.5 py-1 font-heading fw-semibold" style="font-size: 11px;"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                    </div>
            </div>
                        <div class="tab-pane fade " id="cat-dining" role="tabpanel">
                <div class="row g-4">
                                        <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-2.5 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="font-size: 10px;">
                                    GODWIN DIRECT
                                </span>
                                <span class="position-absolute top-0 end-0 m-2.5 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 4.9                                </span>
                                <a href="{{ route('store.catalog') }}">
                                    <img src="https://images.unsplash.com/photo-1617806118233-18e1de247200?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block" style="height: 190px;" alt="Imperial 6-Seater Steel Dining Set">
                                </a>
                            </div>
                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">Steel & Solid Wood</span>
                                    <a href="{{ route('store.catalog') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-2 text-truncate" title="Imperial 6-Seater Steel Dining Set" style="font-size: 13px; line-height: 1.4;">Imperial 6-Seater Steel Dining Set</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark" style="font-size: 14px;">₹34,999</span>
                                        <span class="text-muted text-decoration-line-through small ms-1" style="font-size: 10px;">₹49,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-2.5 py-1 font-heading fw-semibold" style="font-size: 11px;"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                        <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-2.5 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="font-size: 10px;">
                                    GODWIN DIRECT
                                </span>
                                <span class="position-absolute top-0 end-0 m-2.5 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 5                                </span>
                                <a href="{{ route('store.catalog') }}">
                                    <img src="https://images.unsplash.com/photo-1615066390971-03e4e1c36ddf?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block" style="height: 190px;" alt="Teak & Metal Dining Table 8-Seater">
                                </a>
                            </div>
                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">Central Teak Top</span>
                                    <a href="{{ route('store.catalog') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-2 text-truncate" title="Teak & Metal Dining Table 8-Seater" style="font-size: 13px; line-height: 1.4;">Teak & Metal Dining Table 8-Seater</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark" style="font-size: 14px;">₹48,500</span>
                                        <span class="text-muted text-decoration-line-through small ms-1" style="font-size: 10px;">₹68,000</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-2.5 py-1 font-heading fw-semibold" style="font-size: 11px;"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                        <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-2.5 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="font-size: 10px;">
                                    GODWIN DIRECT
                                </span>
                                <span class="position-absolute top-0 end-0 m-2.5 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 4.8                                </span>
                                <a href="{{ route('store.catalog') }}">
                                    <img src="https://images.unsplash.com/photo-1577140917170-285929fb55b7?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block" style="height: 190px;" alt="Godwin Compact 4-Seater Dining Set">
                                </a>
                            </div>
                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">Compact Steel Frame</span>
                                    <a href="{{ route('store.catalog') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-2 text-truncate" title="Godwin Compact 4-Seater Dining Set" style="font-size: 13px; line-height: 1.4;">Godwin Compact 4-Seater Dining Set</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark" style="font-size: 14px;">₹22,999</span>
                                        <span class="text-muted text-decoration-line-through small ms-1" style="font-size: 10px;">₹31,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-2.5 py-1 font-heading fw-semibold" style="font-size: 11px;"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                        <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-2.5 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="font-size: 10px;">
                                    GODWIN DIRECT
                                </span>
                                <span class="position-absolute top-0 end-0 m-2.5 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 4.7                                </span>
                                <a href="{{ route('store.catalog') }}">
                                    <img src="https://images.unsplash.com/photo-1617806118233-18e1de247200?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block" style="height: 190px;" alt="Minimalist Steel Frame Dining Benches Set">
                                </a>
                            </div>
                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">Industrial Steel</span>
                                    <a href="{{ route('store.catalog') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-2 text-truncate" title="Minimalist Steel Frame Dining Benches Set" style="font-size: 13px; line-height: 1.4;">Minimalist Steel Frame Dining Benches Set</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark" style="font-size: 14px;">₹18,499</span>
                                        <span class="text-muted text-decoration-line-through small ms-1" style="font-size: 10px;">₹24,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-2.5 py-1 font-heading fw-semibold" style="font-size: 11px;"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                    </div>
            </div>
                        <div class="tab-pane fade " id="cat-desks" role="tabpanel">
                <div class="row g-4">
                                        <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-2.5 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="font-size: 10px;">
                                    GODWIN DIRECT
                                </span>
                                <span class="position-absolute top-0 end-0 m-2.5 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 4.8                                </span>
                                <a href="{{ route('store.catalog') }}">
                                    <img src="https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block" style="height: 190px;" alt="Godwin Executive Ergonomic Steel Desk">
                                </a>
                            </div>
                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">Steel & Walnut Finish</span>
                                    <a href="{{ route('store.catalog') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-2 text-truncate" title="Godwin Executive Ergonomic Steel Desk" style="font-size: 13px; line-height: 1.4;">Godwin Executive Ergonomic Steel Desk</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark" style="font-size: 14px;">₹18,499</span>
                                        <span class="text-muted text-decoration-line-through small ms-1" style="font-size: 10px;">₹24,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-2.5 py-1 font-heading fw-semibold" style="font-size: 11px;"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                        <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-2.5 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="font-size: 10px;">
                                    GODWIN DIRECT
                                </span>
                                <span class="position-absolute top-0 end-0 m-2.5 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 4.9                                </span>
                                <a href="{{ route('store.catalog') }}">
                                    <img src="https://images.unsplash.com/photo-1518455027359-f3f8164ba6bd?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block" style="height: 190px;" alt="Heavy Steel Dual-Motor Standing Desk">
                                </a>
                            </div>
                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">Motorized Height Adjust</span>
                                    <a href="{{ route('store.catalog') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-2 text-truncate" title="Heavy Steel Dual-Motor Standing Desk" style="font-size: 13px; line-height: 1.4;">Heavy Steel Dual-Motor Standing Desk</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark" style="font-size: 14px;">₹32,999</span>
                                        <span class="text-muted text-decoration-line-through small ms-1" style="font-size: 10px;">₹44,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-2.5 py-1 font-heading fw-semibold" style="font-size: 11px;"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                        <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-2.5 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="font-size: 10px;">
                                    GODWIN DIRECT
                                </span>
                                <span class="position-absolute top-0 end-0 m-2.5 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 4.9                                </span>
                                <a href="{{ route('store.catalog') }}">
                                    <img src="https://images.unsplash.com/photo-1595428774223-ef52624120d2?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block" style="height: 190px;" alt="Solid Walnut & Powder Coated Desk">
                                </a>
                            </div>
                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">CRCA Metal Frame</span>
                                    <a href="{{ route('store.catalog') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-2 text-truncate" title="Solid Walnut & Powder Coated Desk" style="font-size: 13px; line-height: 1.4;">Solid Walnut & Powder Coated Desk</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark" style="font-size: 14px;">₹24,999</span>
                                        <span class="text-muted text-decoration-line-through small ms-1" style="font-size: 10px;">₹34,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-2.5 py-1 font-heading fw-semibold" style="font-size: 11px;"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                        <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-2.5 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="font-size: 10px;">
                                    GODWIN DIRECT
                                </span>
                                <span class="position-absolute top-0 end-0 m-2.5 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 4.7                                </span>
                                <a href="{{ route('store.catalog') }}">
                                    <img src="https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block" style="height: 190px;" alt="Modular Office Workstation Desk">
                                </a>
                            </div>
                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">Heavy Steel Wire Cable Track</span>
                                    <a href="{{ route('store.catalog') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-2 text-truncate" title="Modular Office Workstation Desk" style="font-size: 13px; line-height: 1.4;">Modular Office Workstation Desk</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark" style="font-size: 14px;">₹14,999</span>
                                        <span class="text-muted text-decoration-line-through small ms-1" style="font-size: 10px;">₹19,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-2.5 py-1 font-heading fw-semibold" style="font-size: 11px;"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                    </div>
            </div>
                        <div class="tab-pane fade " id="cat-almirahs" role="tabpanel">
                <div class="row g-4">
                                        <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-2.5 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="font-size: 10px;">
                                    GODWIN DIRECT
                                </span>
                                <span class="position-absolute top-0 end-0 m-2.5 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 4.9                                </span>
                                <a href="{{ route('store.catalog') }}">
                                    <img src="https://images.unsplash.com/photo-1595428774223-ef52624120d2?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block" style="height: 190px;" alt="Godwin 3-Door Heavy Metal Almirah">
                                </a>
                            </div>
                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">Powder Coated Steel</span>
                                    <a href="{{ route('store.catalog') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-2 text-truncate" title="Godwin 3-Door Heavy Metal Almirah" style="font-size: 13px; line-height: 1.4;">Godwin 3-Door Heavy Metal Almirah</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark" style="font-size: 14px;">₹28,999</span>
                                        <span class="text-muted text-decoration-line-through small ms-1" style="font-size: 10px;">₹38,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-2.5 py-1 font-heading fw-semibold" style="font-size: 11px;"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                        <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-2.5 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="font-size: 10px;">
                                    GODWIN DIRECT
                                </span>
                                <span class="position-absolute top-0 end-0 m-2.5 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 5                                </span>
                                <a href="{{ route('store.catalog') }}">
                                    <img src="https://images.unsplash.com/photo-1505693314120-0d443867891c?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block" style="height: 190px;" alt="4-Locker Security Steel Storage Almirah">
                                </a>
                            </div>
                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">7-Tank Treated Steel</span>
                                    <a href="{{ route('store.catalog') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-2 text-truncate" title="4-Locker Security Steel Storage Almirah" style="font-size: 13px; line-height: 1.4;">4-Locker Security Steel Storage Almirah</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark" style="font-size: 14px;">₹34,999</span>
                                        <span class="text-muted text-decoration-line-through small ms-1" style="font-size: 10px;">₹47,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-2.5 py-1 font-heading fw-semibold" style="font-size: 11px;"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                        <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-2.5 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="font-size: 10px;">
                                    GODWIN DIRECT
                                </span>
                                <span class="position-absolute top-0 end-0 m-2.5 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 4.9                                </span>
                                <a href="{{ route('store.catalog') }}">
                                    <img src="https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block" style="height: 190px;" alt="Digital Vault Integrated Steel Wardrobe">
                                </a>
                            </div>
                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">Electronic Keypad Lock</span>
                                    <a href="{{ route('store.catalog') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-2 text-truncate" title="Digital Vault Integrated Steel Wardrobe" style="font-size: 13px; line-height: 1.4;">Digital Vault Integrated Steel Wardrobe</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark" style="font-size: 14px;">₹42,000</span>
                                        <span class="text-muted text-decoration-line-through small ms-1" style="font-size: 10px;">₹58,000</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-2.5 py-1 font-heading fw-semibold" style="font-size: 11px;"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                        <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-2.5 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="font-size: 10px;">
                                    GODWIN DIRECT
                                </span>
                                <span class="position-absolute top-0 end-0 m-2.5 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 4.8                                </span>
                                <a href="{{ route('store.catalog') }}">
                                    <img src="https://images.unsplash.com/photo-1595428774223-ef52624120d2?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block" style="height: 190px;" alt="Compact 2-Door Metal Wardrobe">
                                </a>
                            </div>
                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">Matte Grey Powder Finish</span>
                                    <a href="{{ route('store.catalog') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-2 text-truncate" title="Compact 2-Door Metal Wardrobe" style="font-size: 13px; line-height: 1.4;">Compact 2-Door Metal Wardrobe</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark" style="font-size: 14px;">₹19,999</span>
                                        <span class="text-muted text-decoration-line-through small ms-1" style="font-size: 10px;">₹26,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-2.5 py-1 font-heading fw-semibold" style="font-size: 11px;"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                    </div>
            </div>
                        <div class="tab-pane fade " id="cat-recliners" role="tabpanel">
                <div class="row g-4">
                                        <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-2.5 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="font-size: 10px;">
                                    GODWIN DIRECT
                                </span>
                                <span class="position-absolute top-0 end-0 m-2.5 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 5                                </span>
                                <a href="{{ route('store.catalog') }}">
                                    <img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block" style="height: 190px;" alt="Godwin Steel & Velvet Recliner Chair">
                                </a>
                            </div>
                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">Gold Steel & Velvet</span>
                                    <a href="{{ route('store.catalog') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-2 text-truncate" title="Godwin Steel & Velvet Recliner Chair" style="font-size: 13px; line-height: 1.4;">Godwin Steel & Velvet Recliner Chair</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark" style="font-size: 14px;">₹16,999</span>
                                        <span class="text-muted text-decoration-line-through small ms-1" style="font-size: 10px;">₹22,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-2.5 py-1 font-heading fw-semibold" style="font-size: 11px;"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                        <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-2.5 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="font-size: 10px;">
                                    GODWIN DIRECT
                                </span>
                                <span class="position-absolute top-0 end-0 m-2.5 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 4.9                                </span>
                                <a href="{{ route('store.catalog') }}">
                                    <img src="https://images.unsplash.com/photo-1567538096630-e0c55bd6374c?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block" style="height: 190px;" alt="Power Motorized Leather Recliner Armchair">
                                </a>
                            </div>
                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">Italian Cognac Leather</span>
                                    <a href="{{ route('store.catalog') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-2 text-truncate" title="Power Motorized Leather Recliner Armchair" style="font-size: 13px; line-height: 1.4;">Power Motorized Leather Recliner Armchair</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark" style="font-size: 14px;">₹38,999</span>
                                        <span class="text-muted text-decoration-line-through small ms-1" style="font-size: 10px;">₹52,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-2.5 py-1 font-heading fw-semibold" style="font-size: 11px;"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                        <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-2.5 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="font-size: 10px;">
                                    GODWIN DIRECT
                                </span>
                                <span class="position-absolute top-0 end-0 m-2.5 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 4.8                                </span>
                                <a href="{{ route('store.catalog') }}">
                                    <img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block" style="height: 190px;" alt="Ergonomic Executive Swivel Lounge Chair">
                                </a>
                            </div>
                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">High Back Mesh & Steel</span>
                                    <a href="{{ route('store.catalog') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-2 text-truncate" title="Ergonomic Executive Swivel Lounge Chair" style="font-size: 13px; line-height: 1.4;">Ergonomic Executive Swivel Lounge Chair</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark" style="font-size: 14px;">₹24,499</span>
                                        <span class="text-muted text-decoration-line-through small ms-1" style="font-size: 10px;">₹32,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-2.5 py-1 font-heading fw-semibold" style="font-size: 11px;"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                        <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-2.5 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="font-size: 10px;">
                                    GODWIN DIRECT
                                </span>
                                <span class="position-absolute top-0 end-0 m-2.5 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 4.7                                </span>
                                <a href="{{ route('store.catalog') }}">
                                    <img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block" style="height: 190px;" alt="Industrial Steel Accented Lounge Chair">
                                </a>
                            </div>
                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">Matte Black Steel</span>
                                    <a href="{{ route('store.catalog') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-2 text-truncate" title="Industrial Steel Accented Lounge Chair" style="font-size: 13px; line-height: 1.4;">Industrial Steel Accented Lounge Chair</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark" style="font-size: 14px;">₹14,999</span>
                                        <span class="text-muted text-decoration-line-through small ms-1" style="font-size: 10px;">₹19,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-2.5 py-1 font-heading fw-semibold" style="font-size: 11px;"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                    </div>
            </div>
                    </div>
    </section>



    <!-- 9. Interactive Room Lookbook -->
    <section class="container-fluid px-4 px-lg-5 my-5">
        <div class="section-title-editorial">
            <span class="subtitle">INTERACTIVE ROOM LOOKBOOK</span>
            <h2>Click The Hotspots To Shop The Suite</h2>
        </div>
        <div class="lookbook-hotspot-container">
            <img src="https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&q=80&w=1600&h=800" class="w-100 d-block object-fit-cover" style="height: 540px;" alt="Interactive Living Room">
            
            <!-- 1. Main Entrance Door Hotspot -->
            <div class="hotspot-point" style="top: 38%; left: 12%;" title="Bespoke Security Entrance Door" onclick="toggleHotspot('hotspot-door')"></div>
            <div class="hotspot-popover-card" id="hotspot-door" style="top: 20%; left: 14%;">
                <button type="button" class="btn-close-hotspot" onclick="closeHotspot('hotspot-door')" title="Close">&times;</button>
                <div class="d-flex gap-3 align-items-center mb-2 me-3">
                    <img src="https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&q=80&w=150" class="rounded" style="width: 60px; height: 50px; object-fit: cover;" alt="Entrance Door">
                    <div>
                        <span class="badge bg-amber-light text-amber font-heading px-2 py-05 small fw-bold mb-1 d-inline-block" style="font-size: 9px;">MAIN DOOR</span>
                        <h6 class="font-heading fw-bold m-0" style="font-size: 13px;">Bespoke Heavy Steel Entrance Door</h6>
                        <span class="fw-bold text-amber" style="font-size: 14px;">₹24,999</span>
                    </div>
                </div>
                <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury w-100 py-1" style="font-size: 12px;"><i class="fas fa-shopping-bag me-1"></i> Add To Bag</a>
            </div>

            <!-- 2. Top Transom Window Hotspot -->
            <div class="hotspot-point" style="top: 22%; left: 45%;" title="Panoramic Transom Window" onclick="toggleHotspot('hotspot-window-top')"></div>
            <div class="hotspot-popover-card" id="hotspot-window-top" style="top: 15%; left: 32%;">
                <button type="button" class="btn-close-hotspot" onclick="closeHotspot('hotspot-window-top')" title="Close">&times;</button>
                <div class="d-flex gap-3 align-items-center mb-2 me-3">
                    <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&q=80&w=150" class="rounded" style="width: 60px; height: 50px; object-fit: cover;" alt="Panoramic Window">
                    <div>
                        <span class="badge bg-amber-light text-amber font-heading px-2 py-05 small fw-bold mb-1 d-inline-block" style="font-size: 9px;">TRANSOM WINDOW</span>
                        <h6 class="font-heading fw-bold m-0" style="font-size: 13px;">Panoramic Teak & Glass Window</h6>
                        <span class="fw-bold text-amber" style="font-size: 14px;">₹18,999</span>
                    </div>
                </div>
                <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury w-100 py-1" style="font-size: 12px;"><i class="fas fa-shopping-bag me-1"></i> Add To Bag</a>
            </div>

            <!-- 3. Sofa Hotspot -->
            <div class="hotspot-point" style="top: 55%; left: 45%;" title="Alanis Metal Sofa" onclick="toggleHotspot('hotspot-sofa')"></div>
            <div class="hotspot-popover-card" id="hotspot-sofa" style="top: 32%; left: 33%;">
                <button type="button" class="btn-close-hotspot" onclick="closeHotspot('hotspot-sofa')" title="Close">&times;</button>
                <div class="d-flex gap-3 align-items-center mb-2 me-3">
                    <img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=150" class="rounded" style="width: 60px; height: 50px; object-fit: cover;" alt="Sofa">
                    <div>
                        <span class="badge bg-amber-light text-amber font-heading px-2 py-05 small fw-bold mb-1 d-inline-block" style="font-size: 9px;">LIVING SOFA</span>
                        <h6 class="font-heading fw-bold m-0" style="font-size: 13px;">Alanis Metal Frame 3-Seater Sofa</h6>
                        <span class="fw-bold text-amber" style="font-size: 14px;">₹38,999</span>
                    </div>
                </div>
                <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury w-100 py-1" style="font-size: 12px;"><i class="fas fa-shopping-bag me-1"></i> Add To Bag</a>
            </div>

            <!-- 4. Coffee Table Hotspot -->
            <div class="hotspot-point" style="top: 76%; left: 49%;" title="Industrial Steel Coffee Table" onclick="toggleHotspot('hotspot-table')"></div>
            <div class="hotspot-popover-card" id="hotspot-table" style="top: 50%; left: 48%;">
                <button type="button" class="btn-close-hotspot" onclick="closeHotspot('hotspot-table')" title="Close">&times;</button>
                <div class="d-flex gap-3 align-items-center mb-2 me-3">
                    <img src="https://images.unsplash.com/photo-1617806118233-18e1de247200?auto=format&fit=crop&q=80&w=150" class="rounded" style="width: 60px; height: 50px; object-fit: cover;" alt="Table">
                    <div>
                        <span class="badge bg-amber-light text-amber font-heading px-2 py-05 small fw-bold mb-1 d-inline-block" style="font-size: 9px;">COFFEE TABLE</span>
                        <h6 class="font-heading fw-bold m-0" style="font-size: 13px;">Industrial Steel & Teak Coffee Table</h6>
                        <span class="fw-bold text-amber" style="font-size: 14px;">₹16,499</span>
                    </div>
                </div>
                <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury w-100 py-1" style="font-size: 12px;"><i class="fas fa-shopping-bag me-1"></i> Add To Bag</a>
            </div>

            <!-- 5. Accent Armchairs Hotspot -->
            <div class="hotspot-point" style="top: 68%; left: 81%;" title="Italian Leather Accent Chair" onclick="toggleHotspot('hotspot-chairs')"></div>
            <div class="hotspot-popover-card" id="hotspot-chairs" style="top: 42%; left: 63%;">
                <button type="button" class="btn-close-hotspot" onclick="closeHotspot('hotspot-chairs')" title="Close">&times;</button>
                <div class="d-flex gap-3 align-items-center mb-2 me-3">
                    <img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&q=80&w=150" class="rounded" style="width: 60px; height: 50px; object-fit: cover;" alt="Armchair">
                    <div>
                        <span class="badge bg-amber-light text-amber font-heading px-2 py-05 small fw-bold mb-1 d-inline-block" style="font-size: 9px;">ACCENT ARMCHAIR</span>
                        <h6 class="font-heading fw-bold m-0" style="font-size: 13px;">Italian Leather & Steel Accent Chair</h6>
                        <span class="fw-bold text-amber" style="font-size: 14px;">₹22,500</span>
                    </div>
                </div>
                <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury w-100 py-1" style="font-size: 12px;"><i class="fas fa-shopping-bag me-1"></i> Add To Bag</a>
            </div>

            <!-- 6. Side Glass Window Hotspot -->
            <div class="hotspot-point" style="top: 32%; left: 88%;" title="Floor-to-Ceiling Glass Window" onclick="toggleHotspot('hotspot-window-side')"></div>
            <div class="hotspot-popover-card" id="hotspot-window-side" style="top: 20%; left: 68%;">
                <button type="button" class="btn-close-hotspot" onclick="closeHotspot('hotspot-window-side')" title="Close">&times;</button>
                <div class="d-flex gap-3 align-items-center mb-2 me-3">
                    <img src="https://images.unsplash.com/photo-1600566753376-12c8ab7fb75b?auto=format&fit=crop&q=80&w=150" class="rounded" style="width: 60px; height: 50px; object-fit: cover;" alt="Side Window">
                    <div>
                        <span class="badge bg-amber-light text-amber font-heading px-2 py-05 small fw-bold mb-1 d-inline-block" style="font-size: 9px;">GLASS WINDOW</span>
                        <h6 class="font-heading fw-bold m-0" style="font-size: 13px;">Floor-to-Ceiling Teak Glass Window</h6>
                        <span class="fw-bold text-amber" style="font-size: 14px;">₹28,500</span>
                    </div>
                </div>
                <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury w-100 py-1" style="font-size: 12px;"><i class="fas fa-shopping-bag me-1"></i> Add To Bag</a>
            </div>

        </div>
    </section>

        
@include('store.partials.footer')
<!-- Floating WhatsApp Action Button (Icon Only) -->
    <div class="designer-floating-widget">
        <a href="https://wa.me/917418759171" target="_blank" class="rounded-circle text-white d-flex align-items-center justify-content-center shadow-lg text-decoration-none" style="width: 54px; height: 54px; background-color: #25D366; font-size: 28px; transition: transform 0.2s ease, box-shadow 0.2s ease;" title="Chat with Senior Designer on WhatsApp" onmouseover="this.style.transform='scale(1.12)'" onmouseout="this.style.transform='scale(1)'">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>

    <!-- Mobile Bottom App Bar (Fixed at bottom on Mobile Screens) -->
    <nav class="mobile-bottom-nav d-lg-none">
        <a href="{{ route('store.home') }}" class="mobile-nav-item active">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('store.catalog') }}" class="mobile-nav-item">
            <i class="fas fa-compass"></i>
            <span>Explore</span>
        </a>
        <a href="#" class="mobile-nav-item">
            <i class="far fa-heart"></i>
            <span>Wishlist</span>
        </a>
        <a href="{{ route('store.cart') }}" class="mobile-nav-item">
            <i class="fas fa-shopping-bag"></i>
            <span>Bag</span>
            <span class="mobile-nav-badge">2</span>
        </a>
        <a href="#" class="mobile-nav-item" data-bs-toggle="offcanvas" data-bs-target="#mobileNav">
            <i class="far fa-user"></i>
            <span>Account</span>
        </a>
    </nav>

    <!-- Mobile Offcanvas Menu Drawer -->
    <div class="offcanvas offcanvas-start rounded-end-4" tabindex="-1" id="mobileNav">
        <div class="offcanvas-header bg-dark text-white p-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-amber text-white p-3 font-heading fw-bold">GW</div>
                <div>
                    <h6 class="m-0 text-white font-heading fw-bold">Godwin Groups Client</h6>
                    <span class="small text-amber">Welcome Back</span>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-4">
            <h6 class="font-heading fw-bold text-muted small text-uppercase mb-3">Browse Categories</h6>
            <ul class="nav flex-column gap-2 font-heading">
                @forelse ($storeMenu ?? [] as $room)
                    <li class="nav-item">
                        <a class="nav-link text-dark fw-semibold" href="{{ route('store.catalog', ['category' => $room->slug]) }}">
                            <i class="fas fa-couch text-amber me-2"></i> {{ $room->name }}
                        </a>
                        @if ($room->children->isNotEmpty())
                            <ul class="list-unstyled ps-4 mb-2">
                                @foreach ($room->children->take(6) as $child)
                                    <li>
                                        <a class="nav-link py-1 small text-muted" href="{{ route('store.catalog', ['category' => $child->slug]) }}">{{ $child->name }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @empty
                    <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="{{ route('store.catalog') }}">Furniture Catalog</a></li>
                @endforelse
            </ul>
            <hr class="my-4">
            <h6 class="font-heading fw-bold text-muted small text-uppercase mb-3"><i class="fas fa-ellipsis-v me-2 text-amber"></i> More Services & Info</h6>
            <div class="d-flex flex-column gap-1 font-heading">
                <a class="nav-link text-dark py-1" href="#">Online Gift Card</a>
                <a class="nav-link text-dark py-1" href="#">Bulk orders</a>
                <a class="nav-link text-dark py-1" href="#">Corporate Gifts</a>
                <a class="nav-link text-dark py-1" href="#">Offline Gift Card</a>
                <a class="nav-link py-1 item-highlighted" href="#">Homecentre Delight</a>
                <a class="nav-link text-dark py-1" href="{{ route('store.catalog') }}">Catalogues</a>
                <a class="nav-link text-dark py-1" href="#">Blog</a>
                <a class="nav-link text-dark py-1" href="#">Store Locator</a>
                <a class="nav-link text-dark py-1" href="#">Landmark Rewards SBI Credit card</a>
                <a class="nav-link text-dark py-1" href="#">Furniture Exchange</a>
                <a class="nav-link text-dark py-1" href="#">Terms and condition</a>
                <a class="nav-link text-dark py-1" href="#">Landmark Group Foundation</a>
                <a class="nav-link py-1 item-accent" href="#">Gift Card Balance Check</a>
            </div>
            <hr class="my-4">
            <h6 class="font-heading fw-bold text-muted small text-uppercase mb-3">Shop</h6>
            <div class="d-flex flex-column gap-2">
                <a href="https://wa.me/917418759171" target="_blank" class="btn btn-primary-luxury text-start mt-2"><i class="fab fa-whatsapp me-2"></i> WhatsApp Designer Support</a>
            </div>
        </div>
    </div>

    
@endsection
