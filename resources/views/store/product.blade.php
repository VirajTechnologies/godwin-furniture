@extends('layouts.store')

@section('title', 'Godwin Imperial Heavy Duty Metal & Teak Bed')
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
<!-- Breadcrumb -->
    <section class="bg-white border-bottom py-3">
        <div class="container-fluid px-4 px-lg-5">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb m-0 font-heading small">
                    <li class="breadcrumb-item"><a href="{{ route('store.home') }}" class="text-muted text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('store.catalog') }}" class="text-muted text-decoration-none">Bedroom Suites</a></li>
                    <li class="breadcrumb-item active text-amber fw-bold" aria-current="page">Godwin Imperial Heavy Duty Metal & Teak Bed</li>
                </ol>
            </nav>
        </div>
    </section>

    <!-- Product Detail Content -->
    <section class="container-fluid px-4 px-lg-5 my-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-lg-5 bg-white">
            <div class="row g-5">
                
                <!-- Left: Multi-Angle Gallery -->
                <div class="col-12 col-lg-6">
                    <div class="position-relative mb-3 rounded-4 overflow-hidden shadow-sm">
                        <span class="position-absolute top-0 start-0 m-3 badge bg-amber text-white font-heading px-3 py-2 fw-bold" style="font-size: 11px;">#1 BESTSELLER BEDROOM</span>
                        <img id="mainProductImg" src="https://images.unsplash.com/photo-1505693314120-0d443867891c?auto=format&fit=crop&q=80&w=1000" class="img-fluid w-100 object-fit-cover rounded-4" style="min-height: 400px; max-height: 480px;" alt="Godwin Heavy Metal Bed">
                    </div>
                    <!-- Thumbnails -->
                    <div class="row g-2">
                        <div class="col-3">
                            <img src="https://images.unsplash.com/photo-1505693314120-0d443867891c?auto=format&fit=crop&q=80&w=400" class="img-thumbnail rounded-3 cursor-pointer border-amber" onclick="document.getElementById('mainProductImg').src=this.src" alt="Thumbnail 1">
                        </div>
                        <div class="col-3">
                            <img src="https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&q=80&w=400" class="img-thumbnail rounded-3 cursor-pointer opacity-75" onclick="document.getElementById('mainProductImg').src=this.src" alt="Thumbnail 2">
                        </div>
                        <div class="col-3">
                            <img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=400" class="img-thumbnail rounded-3 cursor-pointer opacity-75" onclick="document.getElementById('mainProductImg').src=this.src" alt="Thumbnail 3">
                        </div>
                        <div class="col-3">
                            <img src="https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&q=80&w=400" class="img-thumbnail rounded-3 cursor-pointer opacity-75" onclick="document.getElementById('mainProductImg').src=this.src" alt="Thumbnail 4">
                        </div>
                    </div>
                </div>

                <!-- Right: Product Information & Purchase Panel -->
                <div class="col-12 col-lg-6">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-light text-dark border font-heading px-2.5 py-1">SKU: GW-METAL-BED-09</span>
                        <span class="badge bg-amber-light text-amber font-heading px-2.5 py-1 fw-bold"><i class="fas fa-check-circle me-1"></i> ISO 9001:2015 CERTIFIED</span>
                    </div>

                    <h2 class="font-heading fw-bold text-dark mb-2" style="font-size: 26px; line-height: 1.3;">Godwin Imperial Heavy Duty Metal & Seasoned Teak King Bed</h2>
                    
                    <!-- Rating & Reviews -->
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="text-warning">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            <span class="fw-bold text-dark ms-1">4.9 / 5.0</span>
                        </div>
                        <span class="text-muted small font-heading">• 128 Verified Client Reviews</span>
                    </div>

                    <!-- Pricing Container -->
                    <div class="p-3 bg-light rounded-4 border mb-4">
                        <div class="d-flex align-items-baseline gap-3">
                            <span class="fs-2 fw-bold text-dark font-heading">₹42,999</span>
                            <span class="fs-5 text-muted text-decoration-line-through font-heading">₹59,999</span>
                            <span class="badge bg-success text-white font-heading px-2.5 py-1 fw-bold">SAVE ₹17,000 (28% OFF)</span>
                        </div>
                        <p class="small text-muted font-heading m-0 mt-1"><i class="fas fa-info-circle text-amber me-1"></i> Includes 18% GST (₹6,559) + Free White-Glove Home Installation</p>
                    </div>

                    <!-- Material & Finish Selector -->
                    <div class="mb-4">
                        <label class="font-heading fw-bold text-dark small mb-2 d-block">Frame Material & Finish</label>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-outline-dark active rounded-pill px-3 py-1.5 font-heading small fw-bold"><i class="fas fa-check me-1"></i> CRCA Steel & Seasoned Teak</button>
                            <button class="btn btn-outline-dark rounded-pill px-3 py-1.5 font-heading small">Matte Black Powder Coat</button>
                            <button class="btn btn-outline-dark rounded-pill px-3 py-1.5 font-heading small">Brass Metallic Finish</button>
                        </div>
                    </div>

                    <!-- Pincode Checker -->
                    <div class="mb-4">
                        <label class="font-heading fw-bold text-dark small mb-2 d-block"><i class="fas fa-truck text-amber me-1"></i> Check Delivery & Installation Pincode</label>
                        <div class="input-group style-group" style="max-width: 360px;">
                            <input type="text" class="form-control shadow-none font-heading" placeholder="Enter 6-digit pincode..." value="440001">
                            <button class="btn btn-dark font-heading px-3 fw-bold">Check</button>
                        </div>
                        <span class="small text-success fw-semibold font-heading mt-1 d-block"><i class="fas fa-check-circle me-1"></i> Eligible for Free 48-Hour White-Glove Delivery to Nagpur</span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex flex-column flex-sm-row gap-3 mb-4">
                        <a href="{{ route('store.cart') }}" class="btn btn-primary-luxury btn-lg py-3 px-4 flex-grow-1 font-heading fw-bold shadow-sm"><i class="fas fa-shopping-bag me-2"></i> Add To Bag</a>
                        <a href="{{ route('store.cart') }}" class="btn btn-dark btn-lg py-3 px-4 font-heading fw-bold"><i class="fas fa-bolt me-2 text-warning"></i> Buy Now</a>
                        <a href="https://wa.me/917418759171" target="_blank" class="btn btn-success btn-lg py-3 px-4 font-heading fw-bold text-white" style="background-color: #25D366; border: none;" title="Consult Senior Factory Designer on WhatsApp"><i class="fab fa-whatsapp fs-5"></i></a>
                    </div>

                    <!-- Key Guarantees -->
                    <div class="row g-3 border-top pt-3 text-muted small font-heading">
                        <div class="col-4 d-flex align-items-center gap-2">
                            <i class="fas fa-shield-alt text-amber fs-4"></i>
                            <span>10-Year Heavy Structural Warranty</span>
                        </div>
                        <div class="col-4 d-flex align-items-center gap-2">
                            <i class="fas fa-weight-hanging text-amber fs-4"></i>
                            <span>500kg Load Tested Capacity</span>
                        </div>
                        <div class="col-4 d-flex align-items-center gap-2">
                            <i class="fas fa-tools text-amber fs-4"></i>
                            <span>Free In-Home Installation</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Detailed Specifications & Tabs Section -->
            <div class="mt-5 border-top pt-4">
                <ul class="nav nav-tabs font-heading fw-bold border-bottom" id="prodTabs">
                    <li class="nav-item"><a class="nav-link active text-amber" data-bs-toggle="tab" href="#specsTab"><i class="fas fa-list-alt me-2"></i> Technical Specifications</a></li>
                    <li class="nav-item"><a class="nav-link text-dark" data-bs-toggle="tab" href="#materialTab"><i class="fas fa-industry me-2"></i> Manufacturing & Material</a></li>
                    <li class="nav-item"><a class="nav-link text-dark" data-bs-toggle="tab" href="#reviewsTab"><i class="fas fa-star me-2"></i> Client Reviews (128)</a></li>
                </ul>
                
                <div class="tab-content p-4 bg-light rounded-bottom-4 border-bottom border-start border-end">
                    <!-- Specs Tab -->
                    <div class="tab-pane fade show active" id="specsTab">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <table class="table table-bordered bg-white font-heading small m-0">
                                    <tr><th class="bg-light" style="width: 40%;">Bed Dimensions</th><td>78" Length x 72" Width x 36" Headboard Height</td></tr>
                                    <tr><th class="bg-light">Frame Material</th><td>Cold-Rolled Close-Annealed (CRCA) Heavy Gauge Steel</td></tr>
                                    <tr><th class="bg-light">Wood Panels</th><td>Seasoned Central India Teak Wood (Termite Treated)</td></tr>
                                    <tr><th class="bg-light">Total Weight</th><td>85 kg (Heavy Duty Anti-Squeak Structural Frame)</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-bordered bg-white font-heading small m-0">
                                    <tr><th class="bg-light" style="width: 40%;">Weight Capacity</th><td>Tested for up to 500 kg static load</td></tr>
                                    <tr><th class="bg-light">Coating Finish</th><td>7-Tank Anti-Rust Phosphate + Epoxy Powder Coating</td></tr>
                                    <tr><th class="bg-light">Warranty Coverage</th><td>10 Years Structural Frame Warranty</td></tr>
                                    <tr><th class="bg-light">Assembly Required</th><td>Yes (Provided Free by Godwin Certified Engineers)</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- You May Also Like Section -->
    
    <section class="container-fluid px-4 px-lg-5 my-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-2">
            <div>
                <span class="text-amber font-heading fw-bold small text-uppercase tracking-wider">CURATED RECOMMENDATIONS</span>
                <h3 class="font-heading fw-bold text-dark m-0 fs-2">You May Also Like</h3>
            </div>
            <a href="{{ route('store.catalog') }}" class="btn btn-outline-dark font-heading fw-semibold rounded-pill px-4">View All Products <i class="fas fa-arrow-right ms-1"></i></a>
        </div>

        <div class="row g-4">
                        <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                    <div class="position-relative overflow-hidden">
                        <span class="position-absolute top-0 start-0 m-3 badge bg-amber text-white font-heading px-2.5 py-1.5 fw-bold" style="z-index: 10; font-size: 10px;">
                            #1 BESTSELLER                        </span>
                        <span class="position-absolute top-0 end-0 m-3 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="z-index: 10; font-size: 10px;">
                            <i class="fas fa-star text-warning me-1"></i> 4.9                        </span>

                        <!-- Product Image Carousel -->
                        <div id="relProductCarousel1" class="carousel slide product-card-carousel" data-bs-interval="false">
                            <div class="carousel-indicators mb-2" style="z-index: 12;">
                                                                <button type="button" onclick="goToProductSlide('relProductCarousel1', 0, event)" class="active" aria-label="Slide 1"></button>
                                                                <button type="button" onclick="goToProductSlide('relProductCarousel1', 1, event)" class="" aria-label="Slide 2"></button>
                                                                <button type="button" onclick="goToProductSlide('relProductCarousel1', 2, event)" class="" aria-label="Slide 3"></button>
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
                            <button class="carousel-control-prev product-carousel-btn" type="button" onclick="slideProductCarousel('relProductCarousel1', 'prev', event)">
                                <span class="carousel-control-prev-icon-custom"><i class="fas fa-chevron-left"></i></span>
                            </button>
                            <button class="carousel-control-next product-carousel-btn" type="button" onclick="slideProductCarousel('relProductCarousel1', 'next', event)">
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
                        <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                    <div class="position-relative overflow-hidden">
                        <span class="position-absolute top-0 start-0 m-3 badge bg-danger text-white font-heading px-2.5 py-1.5 fw-bold" style="z-index: 10; font-size: 10px;">
                            ✨ NEW 2026                        </span>
                        <span class="position-absolute top-0 end-0 m-3 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="z-index: 10; font-size: 10px;">
                            <i class="fas fa-star text-warning me-1"></i> 4.9                        </span>

                        <!-- Product Image Carousel -->
                        <div id="relProductCarousel3" class="carousel slide product-card-carousel" data-bs-interval="false">
                            <div class="carousel-indicators mb-2" style="z-index: 12;">
                                                                <button type="button" onclick="goToProductSlide('relProductCarousel3', 0, event)" class="active" aria-label="Slide 1"></button>
                                                                <button type="button" onclick="goToProductSlide('relProductCarousel3', 1, event)" class="" aria-label="Slide 2"></button>
                                                                <button type="button" onclick="goToProductSlide('relProductCarousel3', 2, event)" class="" aria-label="Slide 3"></button>
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
                            <button class="carousel-control-prev product-carousel-btn" type="button" onclick="slideProductCarousel('relProductCarousel3', 'prev', event)">
                                <span class="carousel-control-prev-icon-custom"><i class="fas fa-chevron-left"></i></span>
                            </button>
                            <button class="carousel-control-next product-carousel-btn" type="button" onclick="slideProductCarousel('relProductCarousel3', 'next', event)">
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
                        <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                    <div class="position-relative overflow-hidden">
                        <span class="position-absolute top-0 start-0 m-3 badge bg-primary text-white font-heading px-2.5 py-1.5 fw-bold" style="z-index: 10; font-size: 10px;">
                            HEAVY STEEL                        </span>
                        <span class="position-absolute top-0 end-0 m-3 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="z-index: 10; font-size: 10px;">
                            <i class="fas fa-star text-warning me-1"></i> 4.9                        </span>

                        <!-- Product Image Carousel -->
                        <div id="relProductCarousel7" class="carousel slide product-card-carousel" data-bs-interval="false">
                            <div class="carousel-indicators mb-2" style="z-index: 12;">
                                                                <button type="button" onclick="goToProductSlide('relProductCarousel7', 0, event)" class="active" aria-label="Slide 1"></button>
                                                                <button type="button" onclick="goToProductSlide('relProductCarousel7', 1, event)" class="" aria-label="Slide 2"></button>
                                                                <button type="button" onclick="goToProductSlide('relProductCarousel7', 2, event)" class="" aria-label="Slide 3"></button>
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
                            <button class="carousel-control-prev product-carousel-btn" type="button" onclick="slideProductCarousel('relProductCarousel7', 'prev', event)">
                                <span class="carousel-control-prev-icon-custom"><i class="fas fa-chevron-left"></i></span>
                            </button>
                            <button class="carousel-control-next product-carousel-btn" type="button" onclick="slideProductCarousel('relProductCarousel7', 'next', event)">
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
                        <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bento-card">
                    <div class="position-relative overflow-hidden">
                        <span class="position-absolute top-0 start-0 m-3 badge bg-dark text-white font-heading px-2.5 py-1.5 fw-bold" style="z-index: 10; font-size: 10px;">
                            RECLINER                        </span>
                        <span class="position-absolute top-0 end-0 m-3 badge bg-white text-dark shadow-sm font-heading px-2 py-1 fw-bold" style="z-index: 10; font-size: 10px;">
                            <i class="fas fa-star text-warning me-1"></i> 5                        </span>

                        <!-- Product Image Carousel -->
                        <div id="relProductCarousel6" class="carousel slide product-card-carousel" data-bs-interval="false">
                            <div class="carousel-indicators mb-2" style="z-index: 12;">
                                                                <button type="button" onclick="goToProductSlide('relProductCarousel6', 0, event)" class="active" aria-label="Slide 1"></button>
                                                                <button type="button" onclick="goToProductSlide('relProductCarousel6', 1, event)" class="" aria-label="Slide 2"></button>
                                                                <button type="button" onclick="goToProductSlide('relProductCarousel6', 2, event)" class="" aria-label="Slide 3"></button>
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
                            <button class="carousel-control-prev product-carousel-btn" type="button" onclick="slideProductCarousel('relProductCarousel6', 'prev', event)">
                                <span class="carousel-control-prev-icon-custom"><i class="fas fa-chevron-left"></i></span>
                            </button>
                            <button class="carousel-control-next product-carousel-btn" type="button" onclick="slideProductCarousel('relProductCarousel6', 'next', event)">
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
                    </div>
    </section>

        
@include('store.partials.footer')
@include('store.partials.whatsapp')
@endsection
