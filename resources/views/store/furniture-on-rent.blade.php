@extends('layouts.store')

@section('title', 'Furniture on Rent — Coming Soon')
@section('body_class', 'gw-theme-body')

@section('content')
    @include('store.partials.header')

    <section class="gw-wrap gw-catalog-wrap">
        <nav aria-label="breadcrumb" class="gw-cat-crumbs">
            <a href="{{ route('store.home') }}">Home</a>
            <span>/</span>
            <span>Furniture on Rent</span>
        </nav>

        <div class="text-center py-5 my-4">
            <p class="text-uppercase small fw-semibold mb-2" style="letter-spacing: 0.12em; color: #8a6a3d;">Coming Soon</p>
            <h1 class="gw-cat-heading mb-3">Furniture on Rent</h1>
            <p class="gw-cat-count mx-auto mb-4" style="max-width: 32rem;">
                Flexible rental for homes, offices, events and commercial spaces is on the way.
                Browse our collections to buy meanwhile.
            </p>
            <a href="{{ route('store.catalog') }}" class="gw-btn">Browse collections</a>
        </div>
    </section>

    @include('store.partials.footer')
    @include('store.partials.whatsapp')
@endsection
