@php
    $homeUrl = $homeUrl ?? url('/');
@endphp
<a href="{{ $homeUrl }}" class="logo logo-dark">
    <span class="logo-sm"><img src="{{ asset('store/images/logo.png') }}" alt="GODWIN" class="admin-brand-logo-sm"></span>
    <span class="logo-lg"><img src="{{ asset('store/images/logo.png') }}" alt="GODWIN" class="admin-brand-logo"></span>
</a>
<a href="{{ $homeUrl }}" class="logo logo-light">
    <span class="logo-sm"><img src="{{ asset('store/images/logo.png') }}" alt="GODWIN" class="admin-brand-logo-sm admin-logo-invert"></span>
    <span class="logo-lg"><img src="{{ asset('store/images/logo.png') }}" alt="GODWIN" class="admin-brand-logo admin-logo-invert"></span>
</a>
