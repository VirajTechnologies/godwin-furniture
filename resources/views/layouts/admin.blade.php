<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Admin') | Godwin Furniture</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
    <script src="{{ vasset('assets/js/layout.js') }}"></script>
    <link href="{{ vasset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ vasset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ vasset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ vasset('assets/css/custom.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ vasset('assets/css/required-fields.css') }}" rel="stylesheet" type="text/css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    @include('partials.datatables', ['part' => 'styles'])
    @stack('styles')
</head>
<body>
    <div id="layout-wrapper">
        <header id="page-topbar">
            <div class="layout-width">
                <div class="navbar-header">
                    <div class="d-flex">
                        <div class="navbar-brand-box horizontal-logo">
                            <a href="{{ route('admin.dashboard') }}" class="logo logo-dark">
                                <span class="logo-sm"><span class="fw-bold">G</span></span>
                                <span class="logo-lg"><span class="fw-semibold">Godwin</span></span>
                            </a>
                            <a href="{{ route('admin.dashboard') }}" class="logo logo-light">
                                <span class="logo-sm"><span class="text-white fw-bold">G</span></span>
                                <span class="logo-lg"><span class="text-white fw-semibold">Godwin</span></span>
                            </a>
                        </div>
                        <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger" id="topnav-hamburger-icon">
                            <span class="hamburger-icon">
                                <span></span>
                                <span></span>
                                <span></span>
                            </span>
                        </button>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="dropdown ms-sm-3 header-item topbar-user">
                            <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="d-flex align-items-center">
                                    <img class="rounded-circle header-profile-user" src="{{ asset('assets/images/users/avatar-1.jpg') }}" alt="">
                                    <span class="text-start ms-xl-2">
                                        <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{ auth()->user()->name }}</span>
                                        <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text">Head office</span>
                                    </span>
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <h6 class="dropdown-header">Welcome {{ auth()->user()->name }}</h6>
                                <form method="POST" action="{{ route('admin.logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i>
                                        <span class="align-middle">Logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="app-menu navbar-menu">
            <div class="navbar-brand-box">
                <a href="{{ route('admin.dashboard') }}" class="logo logo-dark">
                    <span class="logo-sm"><span class="fw-bold">G</span></span>
                    <span class="logo-lg"><span class="fw-semibold">Godwin</span></span>
                </a>
                <a href="{{ route('admin.dashboard') }}" class="logo logo-light">
                    <span class="logo-sm"><span class="text-white fw-bold">G</span></span>
                    <span class="logo-lg"><span class="text-white fw-semibold">Godwin</span></span>
                </a>
                <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
                    <i class="ri-record-circle-line"></i>
                </button>
            </div>
            <div id="scrollbar">
                <div class="container-fluid">
                    <div id="two-column-menu"></div>
                    <ul class="navbar-nav" id="navbar-nav">
                        <li class="menu-title"><span>Menu</span></li>
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                <i class="ri-dashboard-2-line"></i> <span>Dashboard</span>
                            </a>
                        </li>
                        @php
                            $mastersOpen = request()->routeIs(
                                'admin.states.*',
                                'admin.districts.*',
                                'admin.cities.*',
                                'admin.business-groups.*',
                                'admin.project-categories.*',
                                'admin.catalog-rooms.*',
                                'admin.catalog-product-categories.*',
                                'admin.design-types.*',
                                'admin.product-models.*',
                                'admin.catalog-config-options.*',
                                'admin.product-colours.*',
                                'admin.product-finishes.*',
                                'admin.product-upholsteries.*',
                            );
                            $registrationOpen = request()->routeIs(
                                'admin.branches.*',
                                'admin.warehouses.*',
                                'admin.employees.*',
                                'admin.agents.*',
                                'admin.customers.*',
                            );
                        @endphp
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ $mastersOpen ? 'active' : '' }}" href="{{ route('admin.states.index') }}">
                                <i class="ri-stack-line"></i> <span>Masters</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ $registrationOpen ? 'active' : '' }}" href="{{ route('admin.branches.index') }}">
                                <i class="ri-user-add-line"></i> <span>Registration</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">
                                <i class="ri-price-tag-3-line"></i> <span>Products</span>
                            </a>
                        </li>
                        @php
                            $inventoryOpen = request()->routeIs('admin.stocks.*', 'admin.branch-stocks.*', 'admin.transfers.*', 'admin.stock-requests.*', 'admin.custom-requests.*');
                        @endphp
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ $inventoryOpen ? 'active' : '' }}" href="{{ route('admin.stocks.index') }}">
                                <i class="ri-archive-line"></i> <span>Inventory</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
                                <i class="ri-bill-line"></i> <span>Sales</span>
                            </a>
                        </li>
                        @php
                            $reportsOpen = request()->routeIs('admin.reports.*');
                        @endphp
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ $reportsOpen ? 'active' : '' }}" href="{{ route('admin.reports.overview') }}">
                                <i class="ri-bar-chart-2-line"></i> <span>Reports &amp; Analytics</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="vertical-overlay"></div>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0">@yield('page_title')</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                                        @yield('breadcrumb')
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>

            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">{{ date('Y') }} © Godwin Furniture.</div>
                        <div class="col-sm-6">
                            <div class="text-sm-end d-none d-sm-block">Head office</div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <button onclick="topFunction()" class="btn btn-danger btn-icon" id="back-to-top">
        <i class="ri-arrow-up-line"></i>
    </button>

    <script src="{{ vasset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ vasset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ vasset('assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ vasset('assets/libs/feather-icons/feather.min.js') }}"></script>
    <script src="{{ vasset('assets/js/plugins.js') }}"></script>
    <script src="{{ vasset('assets/js/app.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @include('partials.datatables')
    @include('partials.csrf-refresh', ['requiresAuth' => true])
    @stack('scripts')
    <script>
        jQuery(function ($) {
            $('.js-select2').each(function () {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    return;
                }

                $(this).select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: $(this).data('placeholder') || 'Select…',
                    allowClear: true,
                });
            });
        });
    </script>
</body>
</html>
