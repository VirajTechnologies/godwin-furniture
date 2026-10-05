@extends('layouts.store')

@section('title', $product->name)
@section('body_class', 'bg-light')

@section('content')
    @include('store.partials.header')

    @php
        $price = $product->storePrice();
        $mrp = $product->mrp();
        $images = $product->images;
        if ($images->isEmpty()) {
            $images = collect([(object) ['url' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=800']]);
        }
        $main = $images->first()->url;
    @endphp

    <section class="bg-white border-bottom py-3">
        <div class="container-fluid px-4 px-lg-5">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb m-0 font-heading small">
                    <li class="breadcrumb-item"><a href="{{ route('store.home') }}" class="text-muted text-decoration-none">Home</a></li>
                    @if ($product->category?->parent)
                        <li class="breadcrumb-item"><a href="{{ route('store.catalog', ['category' => $product->category->parent->slug]) }}" class="text-muted text-decoration-none">{{ $product->category->parent->name }}</a></li>
                    @endif
                    @if ($product->category)
                        <li class="breadcrumb-item"><a href="{{ route('store.catalog', ['category' => $product->category->slug]) }}" class="text-muted text-decoration-none">{{ $product->category->name }}</a></li>
                    @endif
                    <li class="breadcrumb-item active text-amber fw-bold" aria-current="page">{{ $product->name }}</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="container-fluid px-4 px-lg-5 my-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="row g-4">
                <div class="col-12 col-lg-6">
                    <img id="mainProductImg" src="{{ $main }}" class="img-fluid rounded-4 w-100 object-fit-cover mb-3" style="max-height: 480px;" alt="{{ $product->name }}">
                    <div class="row g-2">
                        @foreach ($images as $image)
                            <div class="col-3">
                                <img src="{{ $image->url }}" class="img-thumbnail rounded-3 cursor-pointer {{ $loop->first ? '' : 'opacity-75' }}" onclick="document.getElementById('mainProductImg').src=this.src" alt="{{ $product->name }}">
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-light text-dark border font-heading px-2.5 py-1">SKU: {{ $product->code }}</span>
                        @if ($product->is_featured)
                            <span class="badge bg-amber-light text-amber font-heading px-2.5 py-1 fw-bold">Featured</span>
                        @endif
                    </div>
                    <h2 class="font-heading fw-bold text-dark mb-2" style="font-size: 26px; line-height: 1.3;">{{ $product->name }}</h2>
                    @if ($product->material)
                        <p class="text-muted font-heading small mb-3">{{ $product->material }}</p>
                    @endif
                    <div class="p-3 bg-light rounded-4 border mb-4">
                        <div class="d-flex align-items-baseline gap-3 flex-wrap">
                            <span class="fs-2 fw-bold text-dark font-heading">₹{{ number_format($price, 0) }}</span>
                            @if ($mrp && $mrp > $price)
                                <span class="fs-5 text-muted text-decoration-line-through font-heading">₹{{ number_format($mrp, 0) }}</span>
                                <span class="badge bg-success text-white font-heading px-2.5 py-1 fw-bold">Save ₹{{ number_format($mrp - $price, 0) }}</span>
                            @endif
                        </div>
                    </div>
                    @if ($product->description)
                        <p class="font-heading text-dark mb-4">{{ $product->description }}</p>
                    @endif
                    <div class="d-flex flex-column flex-sm-row gap-3 mb-4">
                        <a href="{{ route('store.cart') }}" class="btn btn-primary-luxury btn-lg py-3 px-4 flex-grow-1 font-heading fw-bold shadow-sm"><i class="fas fa-shopping-bag me-2"></i> Add To Bag</a>
                        <a href="{{ route('store.cart') }}" class="btn btn-dark btn-lg py-3 px-4 font-heading fw-bold"><i class="fas fa-bolt me-2 text-warning"></i> Buy Now</a>
                    </div>
                </div>
            </div>
        </div>

        @if ($related->isNotEmpty())
            <div class="mt-5">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="font-heading fw-bold text-dark m-0">Related Products</h4>
                    <a href="{{ route('store.catalog', ['category' => $product->category?->slug]) }}" class="btn btn-outline-dark font-heading fw-semibold rounded-pill px-4">View All</a>
                </div>
                <div class="row g-4">
                    @foreach ($related as $item)
                        @include('store.partials.product-card', ['product' => $item])
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    @include('store.partials.footer')
    @include('store.partials.whatsapp')
@endsection
