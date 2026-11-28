<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Agent') | Godwin Furniture</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
    <script src="{{ vasset('assets/js/layout.js') }}"></script>
    @include('partials.theme-boot')
    <link href="{{ vasset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ vasset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ vasset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ vasset('assets/css/custom.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ vasset('assets/css/required-fields.css') }}" rel="stylesheet" type="text/css">
</head>
<body>
    <div id="layout-wrapper">
        <header id="page-topbar">
            <div class="layout-width">
                <div class="navbar-header">
                    <div class="d-flex align-items-center">
                        <div class="navbar-brand-box horizontal-logo">
                            @include('partials.brand-logos', ['homeUrl' => route('agent.home')])
                        </div>
                        <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger" id="topnav-hamburger-icon">
                            <span class="hamburger-icon"><span></span><span></span><span></span></span>
                        </button>
                        <a href="{{ route('agent.home') }}" class="admin-topbar-brand d-inline-flex align-items-center ms-1">
                            <img src="{{ asset('store/images/logo.png') }}" alt="GODWIN" class="admin-topbar-logo-img">
                        </a>
                    </div>
                    <div class="d-flex align-items-center">
                        @include('partials.theme-picker')
                        <div class="dropdown ms-sm-3 header-item topbar-user">
                            <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="d-flex align-items-center">
                                    <img class="rounded-circle header-profile-user" src="{{ asset('assets/images/users/avatar-1.jpg') }}" alt="">
                                    <span class="text-start ms-xl-2">
                                        <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{ auth()->user()->name }}</span>
                                        <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text">{{ auth()->user()->isAgentManager() ? 'Manager' : 'Cashier' }} · {{ auth()->user()->agent?->name }}</span>
                                    </span>
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <h6 class="dropdown-header">Welcome {{ auth()->user()->name }}</h6>
                                <form method="POST" action="{{ route('agent.logout') }}">
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
                @include('partials.brand-logos', ['homeUrl' => route('agent.home')])
                <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
                    <i class="ri-record-circle-line"></i>
                </button>
            </div>
            <div id="scrollbar">
                <div class="container-fluid">
                    <ul class="navbar-nav" id="navbar-nav">
                        <li class="menu-title"><span>Menu</span></li>
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('agent.home') ? 'active' : '' }}" href="{{ route('agent.home') }}">
                                <i class="ri-dashboard-2-line"></i> <span>Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('agent.sales.create') ? 'active' : '' }}" href="{{ route('agent.sales.create') }}">
                                <i class="ri-shopping-cart-line"></i> <span>New Sale</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('agent.sales.index', 'agent.sales.show') ? 'active' : '' }}" href="{{ route('agent.sales.index') }}">
                                <i class="ri-bill-line"></i> <span>Sales</span>
                            </a>
                        </li>
                        @php
                            $inventoryOpen = request()->routeIs(
                                'agent.stock.*',
                                'agent.transfers.*',
                                'agent.stock-requests.*',
                                'agent.custom-requests.*',
                            );
                            $reportsOpen = request()->routeIs('agent.reports.*');
                        @endphp
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ $inventoryOpen ? 'active' : '' }}" href="{{ route('agent.stock.index') }}">
                                <i class="ri-archive-line"></i> <span>Inventory</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ $reportsOpen ? 'active' : '' }}" href="{{ route('agent.reports.sales-by-product') }}">
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
                                        <li class="breadcrumb-item"><a href="{{ route('agent.home') }}">Agent</a></li>
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
                            <div class="text-sm-end d-none d-sm-block">{{ auth()->user()->agent?->name }}</div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="{{ vasset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ vasset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ vasset('assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ vasset('assets/libs/feather-icons/feather.min.js') }}"></script>
    <script src="{{ vasset('assets/js/plugins.js') }}"></script>
    <script src="{{ vasset('assets/js/app.js') }}"></script>
    @include('partials.csrf-refresh', ['requiresAuth' => true, 'loginUrl' => route('agent.login')])
    @include('partials.theme-scripts')
    @stack('scripts')
</body>
</html>
