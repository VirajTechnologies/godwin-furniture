<!-- Top Announcement Ticker Bar -->
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

    <!-- Main Header -->
    <header class="header-luxury py-3 bg-white border-bottom">
        <div class="container-fluid px-4 px-lg-5">
            <div class="row align-items-center gy-2">
                <div class="col-12 col-md-3 col-lg-3 d-flex align-items-center justify-content-between">
                    <a href="{{ route('store.home') }}" class="text-decoration-none d-flex align-items-center">
                        <img src="{{ asset('store/images/logo.png') }}" alt="Godwin Groups" style="height: 44px; max-width: 200px; object-fit: contain;">
                    </a>
                    <a href="{{ route('store.cart') }}" class="text-dark position-relative fs-5 d-lg-none">
                        <i class="fas fa-shopping-bag"></i>
                        @if (($cartCount ?? 0) > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 9px;">{{ $cartCount }}</span>
                        @endif
                    </a>
                </div>
                <div class="col-12 col-md-4 col-lg-4 d-none d-md-flex justify-content-center">
                    <form class="w-100 d-flex justify-content-center" action="{{ route('store.catalog') }}" method="GET">
                        <div class="search-box-wrapper">
                            <input type="text" name="q" class="form-control search-input-reduced text-dark shadow-none" placeholder="Search Heavy Metal Beds, Sofas...">
                            <button type="submit" class="search-btn-inside" title="Search"><i class="fas fa-search fs-6"></i></button>
                        </div>
                    </form>
                </div>
                <div class="col-12 col-md-5 col-lg-5 d-none d-lg-flex align-items-center justify-content-end gap-3 gap-xl-4">
                    <a href="#" class="header-icon-item icon-favourites d-flex flex-column align-items-center justify-content-center">
                        <i class="far fa-heart mb-1"></i>
                        <span class="small-text fw-medium">Favourites</span>
                    </a>
                    @auth
                        <a href="{{ route('store.account') }}" class="header-icon-item icon-account d-flex flex-column align-items-center justify-content-center">
                            <i class="far fa-user mb-1"></i>
                            <span class="small-text fw-medium">{{ \Illuminate\Support\Str::limit(auth()->user()->name, 12) }}</span>
                        </a>
                        <form method="POST" action="{{ route('store.logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="header-icon-item border-0 bg-transparent d-flex flex-column align-items-center justify-content-center p-0" title="Sign out">
                                <i class="fas fa-sign-out-alt mb-1"></i>
                                <span class="small-text fw-medium">Sign out</span>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('store.login') }}" class="header-icon-item icon-account d-flex flex-column align-items-center justify-content-center">
                            <i class="far fa-user mb-1"></i>
                            <span class="small-text fw-medium">Account</span>
                        </a>
                    @endauth
                    <a href="{{ route('store.cart') }}" class="header-icon-item icon-basket d-flex flex-column align-items-center justify-content-center">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="fas fa-shopping-bag"></i>
                            @if (($cartCount ?? 0) > 0)
                                <span class="basket-badge-count">{{ $cartCount }}</span>
                            @endif
                        </div>
                        <span class="small-text fw-medium">Basket</span>
                    </a>
                    <!-- More Dropdown -->
                    <div class="dropdown">
                        <a href="#" class="header-icon-item icon-more d-flex flex-column align-items-center justify-content-center" data-bs-toggle="dropdown" aria-expanded="false" id="catMoreDropdown">
                            <i class="fas fa-ellipsis-v mb-1"></i>
                            <span class="small-text fw-semibold">More</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-more border-0 mt-2" aria-labelledby="catMoreDropdown">
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

    <!-- Sticky Navigation Menu Bar (Centered Mega Menu Navigation) -->
    @include('store.partials.mega-nav')


    