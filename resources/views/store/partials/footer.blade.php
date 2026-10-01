<!-- Executive Footer -->
    <footer class="footer-luxury">
        <div class="container-fluid px-4 px-lg-5">
            <div class="row g-4 border-bottom border-secondary border-opacity-25 pb-5">
                <!-- Col 1: Brand & Contact Info -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="bg-white p-2 rounded-3 d-inline-block mb-3">
                        <img src="{{ asset('store/images/logo.png') }}" alt="Godwin Groups Manufacturing" style="height: 42px; max-width: 200px; object-fit: contain;">
                    </div>
                    <p class="small text-slate-400 pe-lg-3 mb-3">Crafting heavy metal, solid teak & executive workplace furniture for Godwin Groups since 1998.</p>
                    <div class="small text-slate-300 d-flex flex-column gap-2 mb-3" style="font-size: 12px;">
                        <div><i class="fas fa-map-marker-alt text-amber me-2"></i> Plot 45, Central Avenue, Industrial Hub, Nagpur</div>
                        <div><i class="fas fa-phone-alt text-amber me-2"></i> +91 7418759171 (Mon-Sat, 9AM-8PM)</div>
                        <div><i class="fas fa-envelope text-amber me-2"></i> support@godwin-groups.com</div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="https://wa.me/917418759171" target="_blank" class="btn btn-sm btn-outline-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"><i class="fab fa-whatsapp"></i></a>
                        <a href="#" class="btn btn-sm btn-outline-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="btn btn-sm btn-outline-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="btn btn-sm btn-outline-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

                <!-- Col 2: System Portals -->
                <div class="col-6 col-md-3 col-lg-2">
                    <h5 class="text-white font-heading fw-bold fs-6 mb-3">Shop</h5>
                    <ul class="list-unstyled mb-0 font-heading" style="font-size: 13px;">
                        <li><a href="{{ route('store.catalog') }}" class="footer-nav-link d-block"><i class="fas fa-shopping-cart me-1.5 text-amber"></i> E-Store Catalog</a></li>
                        <li><a href="{{ route('store.cart') }}" class="footer-nav-link d-block"><i class="fas fa-truck me-1.5 text-amber"></i> Track Order Status</a></li>
                        <li><a href="{{ route('store.catalog') }}" class="footer-nav-link d-block"><i class="fas fa-handshake me-1.5 text-amber"></i> B2B Wholesale</a></li>
                    </ul>
                </div>

                <!-- Col 3: Manufacturing Collections -->
                <div class="col-6 col-md-3 col-lg-2">
                    <h5 class="text-white font-heading fw-bold fs-6 mb-3">Collections</h5>
                    <ul class="list-unstyled mb-0 font-heading" style="font-size: 13px;">
                        <li><a href="{{ route('store.catalog') }}" class="footer-nav-link d-block">🛋️ Living Room Sofas</a></li>
                        <li><a href="{{ route('store.catalog') }}" class="footer-nav-link d-block">🖨️ Heavy Metal Beds</a></li>
                        <li><a href="{{ route('store.catalog') }}" class="footer-nav-link d-block">🍽️ Solid Teak Dining</a></li>
                        <li><a href="{{ route('store.catalog') }}" class="footer-nav-link d-block">💻 Executive Work Desks</a></li>
                        <li><a href="{{ route('store.catalog') }}" class="footer-nav-link d-block">🗄️ Heavy Steel Almirahs</a></li>
                        <li><a href="{{ route('store.catalog') }}" class="footer-nav-link d-block">💡 Brass Lighting Decor</a></li>
                    </ul>
                </div>

                <!-- Col 4: Customer Support & Policies -->
                <div class="col-6 col-md-4 col-lg-2">
                    <h5 class="text-white font-heading fw-bold fs-6 mb-3">Customer Support</h5>
                    <ul class="list-unstyled mb-0 font-heading" style="font-size: 13px;">
                        <li><a href="https://wa.me/917418759171" target="_blank" class="footer-nav-link d-block"><i class="fas fa-tools me-1.5 text-amber"></i> Custom Metal Fab</a></li>
                        <li><a href="{{ route('store.catalog') }}" class="footer-nav-link d-block"><i class="fas fa-shield-alt me-1.5 text-amber"></i> 10-Year Warranty</a></li>
                        <li><a href="{{ route('store.catalog') }}" class="footer-nav-link d-block"><i class="fas fa-boxes me-1.5 text-amber"></i> Easy Returns Policy</a></li>
                        <li><a href="{{ route('store.catalog') }}" class="footer-nav-link d-block"><i class="fas fa-store me-1.5 text-amber"></i> Store Locator</a></li>
                        <li><a href="{{ route('store.catalog') }}" class="footer-nav-link d-block"><i class="fas fa-file-contract me-1.5 text-amber"></i> Terms & Privacy</a></li>
                    </ul>
                </div>

                <!-- Col 5: Priority Club Newsletter & Payments -->
                <div class="col-12 col-md-8 col-lg-3">
                    <h5 class="text-white font-heading fw-bold fs-6 mb-2">Join Priority Club</h5>
                    <p class="small text-slate-400 mb-3">Subscribe for direct factory pricing alerts & exclusive design catalog updates.</p>
                    <form class="d-flex gap-2 mb-3">
                        <input type="email" class="form-control footer-newsletter-input shadow-none" placeholder="Enter your email address...">
                        <button class="btn btn-primary-luxury px-3 py-2 fw-semibold" style="font-size: 13px;">Join</button>
                    </form>
                    <div class="small text-slate-400 fw-semibold mb-2" style="font-size: 11px;">Supported Payment Modes:</div>
                    <div class="d-flex gap-2 flex-wrap text-white">
                        <span class="badge bg-secondary bg-opacity-50 px-2 py-1" style="font-size: 10px;"><i class="fas fa-mobile-alt me-1 text-amber"></i> UPI / GPay</span>
                        <span class="badge bg-secondary bg-opacity-50 px-2 py-1" style="font-size: 10px;"><i class="far fa-credit-card me-1 text-amber"></i> Credit Card</span>
                        <span class="badge bg-secondary bg-opacity-50 px-2 py-1" style="font-size: 10px;"><i class="fas fa-university me-1 text-amber"></i> NetBanking</span>
                    </div>
                </div>
            </div>
            
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between pt-4 small text-slate-400">
                <p class="m-0">© 2026 Godwin Groups Metal Furniture Manufacturing. All rights reserved.</p>
                <div class="d-flex gap-3 mt-2 mt-md-0">
                    <a href="{{ route('store.catalog') }}" class="text-slate-400 text-decoration-none">Privacy Policy</a>
                    <span class="opacity-25">•</span>
                    <a href="{{ route('store.catalog') }}" class="text-slate-400 text-decoration-none">Terms of Service</a>
                    <span class="opacity-25">•</span>
                    <a href="{{ route('store.catalog') }}" class="text-slate-400 text-decoration-none">Sitemap</a>
                </div>
            </div>
        </div>
    </footer>

    