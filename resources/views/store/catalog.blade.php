@extends('layouts.store')

@section('title', 'Furniture Catalog')
@section('body_class', 'bg-light')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function slideProductCarousel(carouselId, direction, event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            var elem = document.getElementById(carouselId);
            if (!elem) return;

            if (typeof bootstrap !== 'undefined' && bootstrap.Carousel) {
                var carouselInstance = bootstrap.Carousel.getOrCreateInstance(elem, { interval: false });
                if (direction === 'prev') {
                    carouselInstance.prev();
                } else {
                    carouselInstance.next();
                }
                return;
            }

            var items = elem.querySelectorAll('.carousel-item');
            var indicators = elem.querySelectorAll('.carousel-indicators button');
            var activeIdx = 0;

            items.forEach(function(item, idx) {
                if (item.classList.contains('active')) {
                    activeIdx = idx;
                }
            });

            items[activeIdx].classList.remove('active');
            if (indicators[activeIdx]) indicators[activeIdx].classList.remove('active');

            var nextIdx;
            if (direction === 'prev') {
                nextIdx = (activeIdx - 1 + items.length) % items.length;
            } else {
                nextIdx = (activeIdx + 1) % items.length;
            }

            items[nextIdx].classList.add('active');
            if (indicators[nextIdx]) indicators[nextIdx].classList.add('active');
        }

        function goToProductSlide(carouselId, slideIndex, event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            var elem = document.getElementById(carouselId);
            if (!elem) return;

            if (typeof bootstrap !== 'undefined' && bootstrap.Carousel) {
                var carouselInstance = bootstrap.Carousel.getOrCreateInstance(elem, { interval: false });
                carouselInstance.to(slideIndex);
                return;
            }

            var items = elem.querySelectorAll('.carousel-item');
            var indicators = elem.querySelectorAll('.carousel-indicators button');

            items.forEach(function(item, idx) {
                if (idx === slideIndex) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });

            indicators.forEach(function(ind, idx) {
                if (idx === slideIndex) {
                    ind.classList.add('active');
                } else {
                    ind.classList.remove('active');
                }
            });
        }
    </script>

@endpush
@section('content')
@include('store.partials.header')
<!-- Breadcrumb & Header Banner -->
    <section class="bg-white border-bottom py-3">
        <div class="container-fluid px-4 px-lg-5">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb m-0 font-heading small">
                    <li class="breadcrumb-item"><a href="{{ route('store.home') }}" class="text-muted text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active text-amber fw-bold" aria-current="page">Furniture Catalog & Collections</li>
                </ol>
            </nav>
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mt-2">
                <div>
                    <h2 class="font-heading fw-bold text-dark m-0">Furniture Catalog</h2>
                    <p class="text-muted small m-0">Explore 450+ heavy metal, solid teak & executive workplace furniture models crafted in India.</p>
                </div>
                <div class="mt-2 mt-md-0 d-flex gap-2">
                    <span class="badge bg-amber-light text-amber font-heading px-3 py-2 fw-bold"><i class="fas fa-certificate me-1"></i> ISO 9001:2015 Certified Manufacturing</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Catalog Main Body (Sidebar Filters + Products Grid) -->
    <section class="container-fluid px-4 px-lg-5 my-4">
        <div class="row g-4">
            
            <!-- Sidebar Filter Panel -->
            <div class="col-12 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 80px; z-index: 10;">
                    <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-3">
                        <h6 class="font-heading fw-bold text-dark m-0"><i class="fas fa-sliders-h text-amber me-2"></i> Filter Products</h6>
                        <a href="{{ route('store.catalog') }}" class="text-amber small text-decoration-none fw-semibold">Reset All</a>
                    </div>

                    <!-- Category Filter -->
                    <div class="mb-4">
                        <label class="font-heading fw-bold small text-dark mb-2 d-block">Category</label>
                        <div class="d-flex flex-column gap-2">
                            <label class="form-check-label small d-flex justify-content-between align-items-center">
                                <span><input type="checkbox" class="form-check-input me-2" checked> Living Room Sofas</span>
                                <span class="badge bg-light text-muted">140</span>
                            </label>
                            <label class="form-check-label small d-flex justify-content-between align-items-center">
                                <span><input type="checkbox" class="form-check-input me-2"> Heavy Duty Metal Beds</span>
                                <span class="badge bg-light text-muted">85</span>
                            </label>
                            <label class="form-check-label small d-flex justify-content-between align-items-center">
                                <span><input type="checkbox" class="form-check-input me-2"> Solid Teak Dining Sets</span>
                                <span class="badge bg-light text-muted">60</span>
                            </label>
                            <label class="form-check-label small d-flex justify-content-between align-items-center">
                                <span><input type="checkbox" class="form-check-input me-2"> Executive Ergonomic Desks</span>
                                <span class="badge bg-light text-muted">45</span>
                            </label>
                            <label class="form-check-label small d-flex justify-content-between align-items-center">
                                <span><input type="checkbox" class="form-check-input me-2"> Heavy Steel Almirahs</span>
                                <span class="badge bg-light text-muted">30</span>
                            </label>
                        </div>
                    </div>

                    <!-- Material Filter -->
                    <div class="mb-4 border-top pt-3">
                        <label class="font-heading fw-bold small text-dark mb-2 d-block">Material & Build</label>
                        <div class="d-flex flex-wrap gap-1.5">
                            <span class="badge bg-dark text-white p-2 border cursor-pointer fw-semibold">CRCA Heavy Steel</span>
                            <span class="badge bg-light text-dark p-2 border cursor-pointer fw-semibold">Solid Teak Wood</span>
                            <span class="badge bg-light text-dark p-2 border cursor-pointer fw-semibold">Italian Leather</span>
                            <span class="badge bg-light text-dark p-2 border cursor-pointer fw-semibold">Powder Coated Brass</span>
                        </div>
                    </div>

                    <!-- Price Range Slider -->
                    <div class="mb-4 border-top pt-3">
                        <label class="font-heading fw-bold small text-dark mb-2 d-block">Price Range (₹)</label>
                        <input type="range" class="form-range" min="5000" max="150000" value="80000" id="priceRange">
                        <div class="d-flex justify-content-between small text-muted font-heading fw-semibold mt-1">
                            <span>₹5,000</span>
                            <span class="text-amber fw-bold">₹80,000</span>
                            <span>₹1,50,000+</span>
                        </div>
                    </div>

                    <button class="btn btn-primary-luxury w-100 py-2 font-heading fw-bold small"><i class="fas fa-filter me-1"></i> Apply Filters</button>
                </div>
            </div>

            <!-- Products Grid Panel -->
            <div class="col-12 col-lg-9">
                
                <!-- Sorting Header Bar -->
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <span class="small font-heading fw-semibold text-muted">Showing <strong class="text-dark">8 of 450</strong> Furniture Products</span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="small font-heading fw-semibold text-muted me-1">Sort By:</span>
                            <select class="form-select form-select-sm shadow-none font-heading fw-semibold text-dark" style="width: 200px;">
                                <option selected>🔥 Popular & Bestseller</option>
                                <option>Price: Low to High</option>
                                <option>Price: High to Low</option>
                                <option>Rating: High to Low</option>
                                <option>Newest 2026 Models</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Products Grid (4 Columns) -->
                <div class="row g-4">
                                        <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-3 badge bg-amber text-white font-heading px-2.5 py-1.5 fw-bold" style="z-index: 10; font-size: 10px;">
                                    #1 BESTSELLER                                </span>
                                <span class="position-absolute top-0 end-0 m-3 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="z-index: 10; font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 4.9                                </span>

                                <!-- Product Image Carousel -->
                                <div id="productCarousel1" class="carousel slide product-card-carousel" data-bs-interval="false">
                                    <div class="carousel-indicators mb-2" style="z-index: 12;">
                                                                                <button type="button" onclick="goToProductSlide('productCarousel1', 0, event)" class="active" aria-label="Slide 1"></button>
                                                                                <button type="button" onclick="goToProductSlide('productCarousel1', 1, event)" class="" aria-label="Slide 2"></button>
                                                                                <button type="button" onclick="goToProductSlide('productCarousel1', 2, event)" class="" aria-label="Slide 3"></button>
                                                                            </div>
                                    <div class="carousel-inner">
                                                                                <div class="carousel-item active">
                                            <a href="{{ route('store.product') }}">
                                                <img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block w-100" style="height: 220px;" alt="Alanis Metal Frame 3-Seater Velvet Sofa">
                                            </a>
                                        </div>
                                                                                <div class="carousel-item ">
                                            <a href="{{ route('store.product') }}">
                                                <img src="https://images.unsplash.com/photo-1493663284031-b7e3aefcae8e?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block w-100" style="height: 220px;" alt="Alanis Metal Frame 3-Seater Velvet Sofa">
                                            </a>
                                        </div>
                                                                                <div class="carousel-item ">
                                            <a href="{{ route('store.product') }}">
                                                <img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block w-100" style="height: 220px;" alt="Alanis Metal Frame 3-Seater Velvet Sofa">
                                            </a>
                                        </div>
                                                                            </div>
                                    <button class="carousel-control-prev product-carousel-btn" type="button" onclick="slideProductCarousel('productCarousel1', 'prev', event)">
                                        <span class="carousel-control-prev-icon-custom"><i class="fas fa-chevron-left"></i></span>
                                    </button>
                                    <button class="carousel-control-next product-carousel-btn" type="button" onclick="slideProductCarousel('productCarousel1', 'next', event)">
                                        <span class="carousel-control-next-icon-custom"><i class="fas fa-chevron-right"></i></span>
                                    </button>
                                </div>
                            </div>

                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">CRCA Steel & Emerald Velvet</span>
                                    <a href="{{ route('store.product') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-3 text-truncate" title="Alanis Metal Frame 3-Seater Velvet Sofa" style="line-height: 1.4;">Alanis Metal Frame 3-Seater Velvet Sofa</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold fs-5 text-dark">₹38,999</span>
                                        <span class="text-muted text-decoration-line-through small ms-1">₹54,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-3 py-2 font-heading fw-semibold"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-3 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="z-index: 10; font-size: 10px;">
                                    TOP RATED                                </span>
                                <span class="position-absolute top-0 end-0 m-3 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="z-index: 10; font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 4.9                                </span>

                                <!-- Product Image Carousel -->
                                <div id="productCarousel2" class="carousel slide product-card-carousel" data-bs-interval="false">
                                    <div class="carousel-indicators mb-2" style="z-index: 12;">
                                                                                <button type="button" onclick="goToProductSlide('productCarousel2', 0, event)" class="active" aria-label="Slide 1"></button>
                                                                                <button type="button" onclick="goToProductSlide('productCarousel2', 1, event)" class="" aria-label="Slide 2"></button>
                                                                                <button type="button" onclick="goToProductSlide('productCarousel2', 2, event)" class="" aria-label="Slide 3"></button>
                                                                            </div>
                                    <div class="carousel-inner">
                                                                                <div class="carousel-item active">
                                            <a href="{{ route('store.product') }}">
                                                <img src="https://images.unsplash.com/photo-1505693314120-0d443867891c?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block w-100" style="height: 220px;" alt="Godwin Heavy Metal & Teak King Bed">
                                            </a>
                                        </div>
                                                                                <div class="carousel-item ">
                                            <a href="{{ route('store.product') }}">
                                                <img src="https://images.unsplash.com/photo-1540518614846-7ede433c5163?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block w-100" style="height: 220px;" alt="Godwin Heavy Metal & Teak King Bed">
                                            </a>
                                        </div>
                                                                                <div class="carousel-item ">
                                            <a href="{{ route('store.product') }}">
                                                <img src="https://images.unsplash.com/photo-1595428774223-ef52624120d2?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block w-100" style="height: 220px;" alt="Godwin Heavy Metal & Teak King Bed">
                                            </a>
                                        </div>
                                                                            </div>
                                    <button class="carousel-control-prev product-carousel-btn" type="button" onclick="slideProductCarousel('productCarousel2', 'prev', event)">
                                        <span class="carousel-control-prev-icon-custom"><i class="fas fa-chevron-left"></i></span>
                                    </button>
                                    <button class="carousel-control-next product-carousel-btn" type="button" onclick="slideProductCarousel('productCarousel2', 'next', event)">
                                        <span class="carousel-control-next-icon-custom"><i class="fas fa-chevron-right"></i></span>
                                    </button>
                                </div>
                            </div>

                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">Powder Coated Steel & Seasoned Teak</span>
                                    <a href="{{ route('store.product') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-3 text-truncate" title="Godwin Heavy Metal & Teak King Bed" style="line-height: 1.4;">Godwin Heavy Metal & Teak King Bed</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold fs-5 text-dark">₹44,499</span>
                                        <span class="text-muted text-decoration-line-through small ms-1">₹59,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-3 py-2 font-heading fw-semibold"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-3 badge bg-danger text-white font-heading px-2.5 py-1.5 fw-bold" style="z-index: 10; font-size: 10px;">
                                    ✨ NEW 2026                                </span>
                                <span class="position-absolute top-0 end-0 m-3 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="z-index: 10; font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 4.9                                </span>

                                <!-- Product Image Carousel -->
                                <div id="productCarousel3" class="carousel slide product-card-carousel" data-bs-interval="false">
                                    <div class="carousel-indicators mb-2" style="z-index: 12;">
                                                                                <button type="button" onclick="goToProductSlide('productCarousel3', 0, event)" class="active" aria-label="Slide 1"></button>
                                                                                <button type="button" onclick="goToProductSlide('productCarousel3', 1, event)" class="" aria-label="Slide 2"></button>
                                                                                <button type="button" onclick="goToProductSlide('productCarousel3', 2, event)" class="" aria-label="Slide 3"></button>
                                                                            </div>
                                    <div class="carousel-inner">
                                                                                <div class="carousel-item active">
                                            <a href="{{ route('store.product') }}">
                                                <img src="https://images.unsplash.com/photo-1617806118233-18e1de247200?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block w-100" style="height: 220px;" alt="Imperial 6-Seater Steel Dining Set">
                                            </a>
                                        </div>
                                                                                <div class="carousel-item ">
                                            <a href="{{ route('store.product') }}">
                                                <img src="https://images.unsplash.com/photo-1615066390971-03e4e1c36ddf?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block w-100" style="height: 220px;" alt="Imperial 6-Seater Steel Dining Set">
                                            </a>
                                        </div>
                                                                                <div class="carousel-item ">
                                            <a href="{{ route('store.product') }}">
                                                <img src="https://images.unsplash.com/photo-1577140917170-285929fb55b7?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block w-100" style="height: 220px;" alt="Imperial 6-Seater Steel Dining Set">
                                            </a>
                                        </div>
                                                                            </div>
                                    <button class="carousel-control-prev product-carousel-btn" type="button" onclick="slideProductCarousel('productCarousel3', 'prev', event)">
                                        <span class="carousel-control-prev-icon-custom"><i class="fas fa-chevron-left"></i></span>
                                    </button>
                                    <button class="carousel-control-next product-carousel-btn" type="button" onclick="slideProductCarousel('productCarousel3', 'next', event)">
                                        <span class="carousel-control-next-icon-custom"><i class="fas fa-chevron-right"></i></span>
                                    </button>
                                </div>
                            </div>

                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">Industrial Steel & Solid Wood</span>
                                    <a href="{{ route('store.product') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-3 text-truncate" title="Imperial 6-Seater Steel Dining Set" style="line-height: 1.4;">Imperial 6-Seater Steel Dining Set</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold fs-5 text-dark">₹34,999</span>
                                        <span class="text-muted text-decoration-line-through small ms-1">₹49,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-3 py-2 font-heading fw-semibold"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-3 badge bg-secondary text-white font-heading px-2.5 py-1.5 fw-bold" style="z-index: 10; font-size: 10px;">
                                    POPULAR                                </span>
                                <span class="position-absolute top-0 end-0 m-3 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="z-index: 10; font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 4.8                                </span>

                                <!-- Product Image Carousel -->
                                <div id="productCarousel4" class="carousel slide product-card-carousel" data-bs-interval="false">
                                    <div class="carousel-indicators mb-2" style="z-index: 12;">
                                                                                <button type="button" onclick="goToProductSlide('productCarousel4', 0, event)" class="active" aria-label="Slide 1"></button>
                                                                                <button type="button" onclick="goToProductSlide('productCarousel4', 1, event)" class="" aria-label="Slide 2"></button>
                                                                                <button type="button" onclick="goToProductSlide('productCarousel4', 2, event)" class="" aria-label="Slide 3"></button>
                                                                            </div>
                                    <div class="carousel-inner">
                                                                                <div class="carousel-item active">
                                            <a href="{{ route('store.product') }}">
                                                <img src="https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block w-100" style="height: 220px;" alt="Godwin Executive Ergonomic Steel Desk">
                                            </a>
                                        </div>
                                                                                <div class="carousel-item ">
                                            <a href="{{ route('store.product') }}">
                                                <img src="https://images.unsplash.com/photo-1518455027359-f3f8164ba6bd?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block w-100" style="height: 220px;" alt="Godwin Executive Ergonomic Steel Desk">
                                            </a>
                                        </div>
                                                                                <div class="carousel-item ">
                                            <a href="{{ route('store.product') }}">
                                                <img src="https://images.unsplash.com/photo-1595428774223-ef52624120d2?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block w-100" style="height: 220px;" alt="Godwin Executive Ergonomic Steel Desk">
                                            </a>
                                        </div>
                                                                            </div>
                                    <button class="carousel-control-prev product-carousel-btn" type="button" onclick="slideProductCarousel('productCarousel4', 'prev', event)">
                                        <span class="carousel-control-prev-icon-custom"><i class="fas fa-chevron-left"></i></span>
                                    </button>
                                    <button class="carousel-control-next product-carousel-btn" type="button" onclick="slideProductCarousel('productCarousel4', 'next', event)">
                                        <span class="carousel-control-next-icon-custom"><i class="fas fa-chevron-right"></i></span>
                                    </button>
                                </div>
                            </div>

                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">CRCA Metal Frame & Walnut Finish</span>
                                    <a href="{{ route('store.product') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-3 text-truncate" title="Godwin Executive Ergonomic Steel Desk" style="line-height: 1.4;">Godwin Executive Ergonomic Steel Desk</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold fs-5 text-dark">₹18,499</span>
                                        <span class="text-muted text-decoration-line-through small ms-1">₹24,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-3 py-2 font-heading fw-semibold"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-3 badge bg-amber text-white font-heading px-2.5 py-1.5 fw-bold" style="z-index: 10; font-size: 10px;">
                                    HOT DEAL                                </span>
                                <span class="position-absolute top-0 end-0 m-3 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="z-index: 10; font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 4.9                                </span>

                                <!-- Product Image Carousel -->
                                <div id="productCarousel5" class="carousel slide product-card-carousel" data-bs-interval="false">
                                    <div class="carousel-indicators mb-2" style="z-index: 12;">
                                                                                <button type="button" onclick="goToProductSlide('productCarousel5', 0, event)" class="active" aria-label="Slide 1"></button>
                                                                                <button type="button" onclick="goToProductSlide('productCarousel5', 1, event)" class="" aria-label="Slide 2"></button>
                                                                                <button type="button" onclick="goToProductSlide('productCarousel5', 2, event)" class="" aria-label="Slide 3"></button>
                                                                            </div>
                                    <div class="carousel-inner">
                                                                                <div class="carousel-item active">
                                            <a href="{{ route('store.product') }}">
                                                <img src="https://images.unsplash.com/photo-1595428774223-ef52624120d2?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block w-100" style="height: 220px;" alt="Modular Powder-Coated Steel Bookshelf">
                                            </a>
                                        </div>
                                                                                <div class="carousel-item ">
                                            <a href="{{ route('store.product') }}">
                                                <img src="https://images.unsplash.com/photo-1594620302200-9a762244a156?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block w-100" style="height: 220px;" alt="Modular Powder-Coated Steel Bookshelf">
                                            </a>
                                        </div>
                                                                                <div class="carousel-item ">
                                            <a href="{{ route('store.product') }}">
                                                <img src="https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block w-100" style="height: 220px;" alt="Modular Powder-Coated Steel Bookshelf">
                                            </a>
                                        </div>
                                                                            </div>
                                    <button class="carousel-control-prev product-carousel-btn" type="button" onclick="slideProductCarousel('productCarousel5', 'prev', event)">
                                        <span class="carousel-control-prev-icon-custom"><i class="fas fa-chevron-left"></i></span>
                                    </button>
                                    <button class="carousel-control-next product-carousel-btn" type="button" onclick="slideProductCarousel('productCarousel5', 'next', event)">
                                        <span class="carousel-control-next-icon-custom"><i class="fas fa-chevron-right"></i></span>
                                    </button>
                                </div>
                            </div>

                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">Matte Black Heavy Duty Steel</span>
                                    <a href="{{ route('store.product') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-3 text-truncate" title="Modular Powder-Coated Steel Bookshelf" style="line-height: 1.4;">Modular Powder-Coated Steel Bookshelf</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold fs-5 text-dark">₹11,999</span>
                                        <span class="text-muted text-decoration-line-through small ms-1">₹16,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-3 py-2 font-heading fw-semibold"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-3 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="z-index: 10; font-size: 10px;">
                                    RECLINER                                </span>
                                <span class="position-absolute top-0 end-0 m-3 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="z-index: 10; font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 5                                </span>

                                <!-- Product Image Carousel -->
                                <div id="productCarousel6" class="carousel slide product-card-carousel" data-bs-interval="false">
                                    <div class="carousel-indicators mb-2" style="z-index: 12;">
                                                                                <button type="button" onclick="goToProductSlide('productCarousel6', 0, event)" class="active" aria-label="Slide 1"></button>
                                                                                <button type="button" onclick="goToProductSlide('productCarousel6', 1, event)" class="" aria-label="Slide 2"></button>
                                                                                <button type="button" onclick="goToProductSlide('productCarousel6', 2, event)" class="" aria-label="Slide 3"></button>
                                                                            </div>
                                    <div class="carousel-inner">
                                                                                <div class="carousel-item active">
                                            <a href="{{ route('store.product') }}">
                                                <img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block w-100" style="height: 220px;" alt="Godwin Steel & Velvet Recliner Chair">
                                            </a>
                                        </div>
                                                                                <div class="carousel-item ">
                                            <a href="{{ route('store.product') }}">
                                                <img src="https://images.unsplash.com/photo-1567538096630-e0c55bd6374c?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block w-100" style="height: 220px;" alt="Godwin Steel & Velvet Recliner Chair">
                                            </a>
                                        </div>
                                                                                <div class="carousel-item ">
                                            <a href="{{ route('store.product') }}">
                                                <img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block w-100" style="height: 220px;" alt="Godwin Steel & Velvet Recliner Chair">
                                            </a>
                                        </div>
                                                                            </div>
                                    <button class="carousel-control-prev product-carousel-btn" type="button" onclick="slideProductCarousel('productCarousel6', 'prev', event)">
                                        <span class="carousel-control-prev-icon-custom"><i class="fas fa-chevron-left"></i></span>
                                    </button>
                                    <button class="carousel-control-next product-carousel-btn" type="button" onclick="slideProductCarousel('productCarousel6', 'next', event)">
                                        <span class="carousel-control-next-icon-custom"><i class="fas fa-chevron-right"></i></span>
                                    </button>
                                </div>
                            </div>

                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">Gold Steel & Velvet Fabric</span>
                                    <a href="{{ route('store.product') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-3 text-truncate" title="Godwin Steel & Velvet Recliner Chair" style="line-height: 1.4;">Godwin Steel & Velvet Recliner Chair</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold fs-5 text-dark">₹16,999</span>
                                        <span class="text-muted text-decoration-line-through small ms-1">₹22,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-3 py-2 font-heading fw-semibold"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-3 badge bg-primary text-white font-heading px-2.5 py-1.5 fw-bold" style="z-index: 10; font-size: 10px;">
                                    HEAVY STEEL                                </span>
                                <span class="position-absolute top-0 end-0 m-3 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="z-index: 10; font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 4.9                                </span>

                                <!-- Product Image Carousel -->
                                <div id="productCarousel7" class="carousel slide product-card-carousel" data-bs-interval="false">
                                    <div class="carousel-indicators mb-2" style="z-index: 12;">
                                                                                <button type="button" onclick="goToProductSlide('productCarousel7', 0, event)" class="active" aria-label="Slide 1"></button>
                                                                                <button type="button" onclick="goToProductSlide('productCarousel7', 1, event)" class="" aria-label="Slide 2"></button>
                                                                                <button type="button" onclick="goToProductSlide('productCarousel7', 2, event)" class="" aria-label="Slide 3"></button>
                                                                            </div>
                                    <div class="carousel-inner">
                                                                                <div class="carousel-item active">
                                            <a href="{{ route('store.product') }}">
                                                <img src="https://images.unsplash.com/photo-1595428774223-ef52624120d2?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block w-100" style="height: 220px;" alt="Godwin 3-Door Heavy Metal Almirah">
                                            </a>
                                        </div>
                                                                                <div class="carousel-item ">
                                            <a href="{{ route('store.product') }}">
                                                <img src="https://images.unsplash.com/photo-1505693314120-0d443867891c?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block w-100" style="height: 220px;" alt="Godwin 3-Door Heavy Metal Almirah">
                                            </a>
                                        </div>
                                                                                <div class="carousel-item ">
                                            <a href="{{ route('store.product') }}">
                                                <img src="https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block w-100" style="height: 220px;" alt="Godwin 3-Door Heavy Metal Almirah">
                                            </a>
                                        </div>
                                                                            </div>
                                    <button class="carousel-control-prev product-carousel-btn" type="button" onclick="slideProductCarousel('productCarousel7', 'prev', event)">
                                        <span class="carousel-control-prev-icon-custom"><i class="fas fa-chevron-left"></i></span>
                                    </button>
                                    <button class="carousel-control-next product-carousel-btn" type="button" onclick="slideProductCarousel('productCarousel7', 'next', event)">
                                        <span class="carousel-control-next-icon-custom"><i class="fas fa-chevron-right"></i></span>
                                    </button>
                                </div>
                            </div>

                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">Powder Coated Steel Locker</span>
                                    <a href="{{ route('store.product') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-3 text-truncate" title="Godwin 3-Door Heavy Metal Almirah" style="line-height: 1.4;">Godwin 3-Door Heavy Metal Almirah</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold fs-5 text-dark">₹28,999</span>
                                        <span class="text-muted text-decoration-line-through small ms-1">₹38,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-3 py-2 font-heading fw-semibold"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                            <div class="position-relative overflow-hidden">
                                <span class="position-absolute top-0 start-0 m-3 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="z-index: 10; font-size: 10px;">
                                    LUXURY                                </span>
                                <span class="position-absolute top-0 end-0 m-3 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="z-index: 10; font-size: 10px;">
                                    <i class="fas fa-star text-warning me-1"></i> 4.8                                </span>

                                <!-- Product Image Carousel -->
                                <div id="productCarousel8" class="carousel slide product-card-carousel" data-bs-interval="false">
                                    <div class="carousel-indicators mb-2" style="z-index: 12;">
                                                                                <button type="button" onclick="goToProductSlide('productCarousel8', 0, event)" class="active" aria-label="Slide 1"></button>
                                                                                <button type="button" onclick="goToProductSlide('productCarousel8', 1, event)" class="" aria-label="Slide 2"></button>
                                                                                <button type="button" onclick="goToProductSlide('productCarousel8', 2, event)" class="" aria-label="Slide 3"></button>
                                                                            </div>
                                    <div class="carousel-inner">
                                                                                <div class="carousel-item active">
                                            <a href="{{ route('store.product') }}">
                                                <img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block w-100" style="height: 220px;" alt="Chesterfield Italian Leather Sofa 3-Seater">
                                            </a>
                                        </div>
                                                                                <div class="carousel-item ">
                                            <a href="{{ route('store.product') }}">
                                                <img src="https://images.unsplash.com/photo-1493663284031-b7e3aefcae8e?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block w-100" style="height: 220px;" alt="Chesterfield Italian Leather Sofa 3-Seater">
                                            </a>
                                        </div>
                                                                                <div class="carousel-item ">
                                            <a href="{{ route('store.product') }}">
                                                <img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&q=80&w=600" class="card-img-top object-fit-cover d-block w-100" style="height: 220px;" alt="Chesterfield Italian Leather Sofa 3-Seater">
                                            </a>
                                        </div>
                                                                            </div>
                                    <button class="carousel-control-prev product-carousel-btn" type="button" onclick="slideProductCarousel('productCarousel8', 'prev', event)">
                                        <span class="carousel-control-prev-icon-custom"><i class="fas fa-chevron-left"></i></span>
                                    </button>
                                    <button class="carousel-control-next product-carousel-btn" type="button" onclick="slideProductCarousel('productCarousel8', 'next', event)">
                                        <span class="carousel-control-next-icon-custom"><i class="fas fa-chevron-right"></i></span>
                                    </button>
                                </div>
                            </div>

                            <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-light text-muted border font-heading px-2 py-1 mb-2 d-inline-block" style="font-size: 10px; font-weight: 600;">Full Grain Italian Cognac Leather</span>
                                    <a href="{{ route('store.product') }}" class="text-decoration-none">
                                        <h5 class="font-heading fw-bold text-dark fs-6 mb-3 text-truncate" title="Chesterfield Italian Leather Sofa 3-Seater" style="line-height: 1.4;">Chesterfield Italian Leather Sofa 3-Seater</h5>
                                    </a>
                                </div>
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold fs-5 text-dark">₹52,999</span>
                                        <span class="text-muted text-decoration-line-through small ms-1">₹74,999</span>
                                    </div>
                                    <a href="{{ route('store.cart') }}" class="btn btn-sm btn-primary-luxury px-3 py-2 font-heading fw-semibold"><i class="fas fa-shopping-bag me-1"></i> Add</a>
                                </div>
                            </div>
                        </div>
                    </div>
                                    </div>

                <!-- Pagination Nav -->
                <div class="d-flex justify-content-center mt-5">
                    <nav>
                        <ul class="pagination font-heading">
                            <li class="page-item disabled"><a class="page-link" href="#"><i class="fas fa-chevron-left"></i></a></li>
                            <li class="page-item active"><a class="page-link bg-dark border-dark" href="#">1</a></li>
                            <li class="page-item"><a class="page-link text-dark" href="#">2</a></li>
                            <li class="page-item"><a class="page-link text-dark" href="#">3</a></li>
                            <li class="page-item"><a class="page-link text-dark" href="#"><i class="fas fa-chevron-right"></i></a></li>
                        </ul>
                    </nav>
                </div>

            </div>
        </div>
    </section>

        
@include('store.partials.footer')
@include('store.partials.whatsapp')
@endsection
