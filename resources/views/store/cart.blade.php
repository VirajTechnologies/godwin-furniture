@extends('layouts.store')

@section('title', 'Shopping Bag')
@section('body_class', 'bg-light')

@section('content')
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
                <a href="{{ route('store.cart') }}"><i class="fas fa-truck me-1 opacity-75"></i> Track Furniture Order</a>
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
                </div>
                <div class="col-12 col-md-6 col-lg-6 text-center">
                    <h4 class="font-heading fw-bold m-0 text-dark">Secure Factory Direct Checkout</h4>
                    <span class="small text-muted"><i class="fas fa-lock text-success me-1"></i> 256-Bit Encrypted Order Processing</span>
                </div>
                <div class="col-12 col-md-3 col-lg-3 text-end d-none d-lg-block">
                    <a href="{{ route('store.catalog') }}" class="text-amber font-heading fw-semibold text-decoration-none small"><i class="fas fa-arrow-left me-1"></i> Continue Shopping</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Shopping Cart & Checkout Layout -->
    <section class="container-fluid px-4 px-lg-5 my-4">
        <div class="row g-4">
            
            <!-- Left: Cart Items & Shipping Address -->
            <div class="col-12 col-lg-8">
                
                <!-- Cart Items Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-3">
                        <h5 class="font-heading fw-bold text-dark m-0"><i class="fas fa-shopping-bag text-amber me-2"></i> Your Shopping Bag (2 Items)</h5>
                        <span class="badge bg-amber-light text-amber font-heading px-2.5 py-1 fw-bold">PROMO APPLIED: GODWIN40</span>
                    </div>

                    <!-- Item 1 -->
                    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 pb-3 border-bottom mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <img src="https://images.unsplash.com/photo-1505693314120-0d443867891c?auto=format&fit=crop&q=80&w=200" class="rounded-3 object-fit-cover" style="width: 90px; height: 90px;" alt="Heavy Metal Bed">
                            <div>
                                <span class="badge bg-light text-muted border font-heading px-2 py-0.5 mb-1" style="font-size: 10px;">CRCA Steel & Seasoned Teak</span>
                                <h6 class="font-heading fw-bold text-dark m-0" style="font-size: 15px;">Godwin Imperial Heavy Duty Metal & Teak Bed</h6>
                                <span class="small text-muted font-heading">Size: King (78" x 72") | Color: Matte Black</span>
                                <div class="mt-1 fw-bold text-dark font-heading">₹42,999 <span class="text-muted text-decoration-line-through small fw-normal">₹59,999</span></div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between justify-content-sm-end gap-4">
                            <div class="input-group style-group" style="width: 100px;">
                                <button class="btn btn-outline-secondary btn-sm px-2">-</button>
                                <input type="text" class="form-control form-control-sm text-center font-heading fw-bold shadow-none" value="1">
                                <button class="btn btn-outline-secondary btn-sm px-2">+</button>
                            </div>
                            <span class="fw-bold font-heading text-dark fs-6">₹42,999</span>
                            <button class="btn btn-link text-danger p-0 border-0 fs-5" title="Remove Item"><i class="far fa-trash-alt"></i></button>
                        </div>
                    </div>

                    <!-- Item 2 -->
                    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=200" class="rounded-3 object-fit-cover" style="width: 90px; height: 90px;" alt="Alanis Velvet Sofa">
                            <div>
                                <span class="badge bg-light text-muted border font-heading px-2 py-0.5 mb-1" style="font-size: 10px;">CRCA Steel & Velvet</span>
                                <h6 class="font-heading fw-bold text-dark m-0" style="font-size: 15px;">Alanis Metal Frame 3-Seater Velvet Sofa</h6>
                                <span class="small text-muted font-heading">Fabric: Emerald Velvet | Frame: Brass Coated</span>
                                <div class="mt-1 fw-bold text-dark font-heading">₹40,499 <span class="text-muted text-decoration-line-through small fw-normal">₹54,999</span></div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between justify-content-sm-end gap-4">
                            <div class="input-group style-group" style="width: 100px;">
                                <button class="btn btn-outline-secondary btn-sm px-2">-</button>
                                <input type="text" class="form-control form-control-sm text-center font-heading fw-bold shadow-none" value="1">
                                <button class="btn btn-outline-secondary btn-sm px-2">+</button>
                            </div>
                            <span class="fw-bold font-heading text-dark fs-6">₹40,499</span>
                            <button class="btn btn-link text-danger p-0 border-0 fs-5" title="Remove Item"><i class="far fa-trash-alt"></i></button>
                        </div>
                    </div>

                </div>

                <!-- Delivery Address Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h5 class="font-heading fw-bold text-dark mb-3"><i class="fas fa-truck text-amber me-2"></i> Shipping & Installation Address</h5>
                    <form action="{{ route('store.cart') }}" method="GET" id="checkoutForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="font-heading fw-bold small text-dark mb-1">Full Name</label>
                                <input type="text" class="form-control font-heading shadow-none" value="Rajesh Kumar" required>
                            </div>
                            <div class="col-md-6">
                                <label class="font-heading fw-bold small text-dark mb-1">Phone Number (For Delivery Updates)</label>
                                <input type="text" class="form-control font-heading shadow-none" value="+91 98230 11223" required>
                            </div>
                            <div class="col-12">
                                <label class="font-heading fw-bold small text-dark mb-1">Street / House Address</label>
                                <input type="text" class="form-control font-heading shadow-none" value="Plot 45, Central Avenue, Industrial Hub" required>
                            </div>
                            <div class="col-md-4">
                                <label class="font-heading fw-bold small text-dark mb-1">City</label>
                                <input type="text" class="form-control font-heading shadow-none" value="Nagpur" required>
                            </div>
                            <div class="col-md-4">
                                <label class="font-heading fw-bold small text-dark mb-1">State</label>
                                <input type="text" class="form-control font-heading shadow-none" value="Maharashtra" required>
                            </div>
                            <div class="col-md-4">
                                <label class="font-heading fw-bold small text-dark mb-1">Pincode</label>
                                <input type="text" class="form-control font-heading shadow-none" value="440001" required>
                            </div>
                        </div>
                    </form>
                </div>

            </div>

            <!-- Right: Order Summary & Payment Options -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 80px;">
                    <h5 class="font-heading fw-bold text-dark pb-3 border-bottom mb-3"><i class="fas fa-receipt text-amber me-2"></i> Order Summary</h5>
                    
                    <div class="d-flex justify-content-between font-heading small mb-2 text-muted">
                        <span>Bag Subtotal (2 Items)</span>
                        <span class="fw-bold text-dark">₹83,498.00</span>
                    </div>

                    <div class="d-flex justify-content-between font-heading small mb-2 text-muted">
                        <span>GST (18% Included)</span>
                        <span class="fw-bold text-dark">₹14,309.64</span>
                    </div>

                    <div class="d-flex justify-content-between font-heading small mb-2 text-success">
                        <span>Factory Direct Promo (GODWIN40)</span>
                        <span class="fw-bold">-₹4,000.00</span>
                    </div>

                    <div class="d-flex justify-content-between font-heading small mb-3 text-muted">
                        <span>White-Glove Delivery & Installation</span>
                        <span class="badge bg-success text-white">FREE</span>
                    </div>

                    <!-- Coupon Code Input -->
                    <div class="input-group mb-3">
                        <input type="text" class="form-control font-heading text-uppercase shadow-none" value="GODWIN40">
                        <button class="btn btn-dark font-heading fw-bold small">Applied</button>
                    </div>

                    <div class="border-top pt-3 mb-4 d-flex justify-content-between align-items-center">
                        <span class="font-heading fw-bold text-dark fs-6">Grand Total:</span>
                        <span class="font-heading fw-bold fs-4 text-dark">₹93,807.64</span>
                    </div>

                    <!-- Payment Mode Selector -->
                    <div class="mb-4">
                        <label class="font-heading fw-bold small text-dark mb-2 d-block">Select Payment Option</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="paymentMode" id="payUPI" checked>
                            <label class="form-check-label font-heading small fw-bold text-dark" for="payUPI">
                                <i class="fas fa-mobile-alt text-amber me-1"></i> Instant UPI / GPay / PhonePe (Fastest)
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="paymentMode" id="payCard">
                            <label class="form-check-label font-heading small fw-bold text-dark" for="payCard">
                                <i class="far fa-credit-card text-amber me-1"></i> Credit / Debit Card (All Indian Cards)
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="paymentMode" id="payEMI">
                            <label class="form-check-label font-heading small fw-bold text-dark" for="payEMI">
                                <i class="fas fa-university text-amber me-1"></i> No Cost EMI (Starts ₹7,817/mo)
                            </label>
                        </div>
                    </div>

                    <!-- Place Order Button -->
                    <button type="button" class="btn btn-primary-luxury w-100 py-3 font-heading fw-bold fs-6 shadow-sm" onclick="alert('🎉 Order Placed Successfully! Your Order ID: GW-ORD-20260731-982. A WhatsApp confirmation has been dispatched.')"><i class="fas fa-check-circle me-2"></i> Place Order & Pay</button>

                    <div class="text-center mt-3 small text-muted font-heading">
                        <i class="fas fa-shield-alt text-amber me-1"></i> 100% Purchase Protection & 10-Year Warranty
                    </div>
                </div>
            </div>

        </div>
    </section>

        
@include('store.partials.footer')
@include('store.partials.whatsapp')
@endsection
