<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Finance ERP</title>
    <meta name="description" content="Finance ERP — Loan Management System">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

    {{-- Custom CSS --}}
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body class="lms-body">

{{-- ─── SIDEBAR ─────────────────────────────────────────────────────── --}}
<div class="lms-sidebar" id="sidebar">
    {{-- Logo --}}
    <div class="sidebar-brand">
        <div class="brand-icon">
            <i class="bi bi-bank2"></i>
        </div>
        <div class="brand-text">
            <span class="brand-name">Finance ERP</span>
            <span class="brand-sub">Loan Management</span>
        </div>
        <button class="btn btn-link sidebar-toggle d-lg-none" id="sidebarClose">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    {{-- User mini-card --}}
    <div class="sidebar-user">
        <div class="user-avatar">
            <img src="{{ auth()->user()->avatar_url ?? asset('images/default-avatar.png') }}" alt="Avatar">
        </div>
        <div class="user-info">
            <span class="user-name">{{ auth()->user()->name }}</span>
            <span class="user-role">{{ auth()->user()->getRoleNames()->first() ?? 'User' }}</span>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="sidebar-nav">
        <ul class="nav-list">

            <li class="nav-section-label">Main</li>

            <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" class="nav-link-item">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            @can('customer.view')
            <li class="nav-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <a href="{{ route('customers.index') }}" class="nav-link-item">
                    <i class="bi bi-people-fill"></i>
                    <span>Customers</span>
                </a>
            </li>
            @endcan

            @can('loan.view')
            <li class="nav-item {{ request()->routeIs('loans.*') ? 'active' : '' }}">
                <a href="{{ route('loans.index') }}" class="nav-link-item">
                    <i class="bi bi-cash-coin"></i>
                    <span>Loans</span>
                </a>
            </li>
            @endcan

            @can('payment.view')
            <li class="nav-section-label">Collections</li>
            <li class="nav-item {{ request()->routeIs('loans.payments.*') ? 'active' : '' }}">
                <a href="{{ route('loans.index') }}?filter=payments" class="nav-link-item">
                    <i class="bi bi-receipt-cutoff"></i>
                    <span>Payments</span>
                </a>
            </li>
            @endcan

            @can('accounting.view')
            <li class="nav-section-label">Finance</li>
            <li class="nav-item {{ request()->routeIs('accounting.*') ? 'active' : '' }}">
                <a href="{{ route('accounting.index') }}" class="nav-link-item">
                    <i class="bi bi-journal-bookmark-fill"></i>
                    <span>Accounting</span>
                </a>
            </li>
            @endcan

            @can('report.loan')
            <li class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <a href="{{ route('reports.index') }}" class="nav-link-item">
                    <i class="bi bi-bar-chart-fill"></i>
                    <span>Reports</span>
                </a>
            </li>
            @endcan

            @can('document.view')
            <li class="nav-section-label">Management</li>
            <li class="nav-item {{ request()->routeIs('documents.*') ? 'active' : '' }}">
                <a href="{{ route('documents.index') }}" class="nav-link-item">
                    <i class="bi bi-folder2-open"></i>
                    <span>Documents</span>
                </a>
            </li>
            @endcan

            @can('branch.view')
            <li class="nav-section-label">Administration</li>
            <li class="nav-item {{ request()->routeIs('branches.*') ? 'active' : '' }}">
                <a href="{{ route('branches.index') }}" class="nav-link-item">
                    <i class="bi bi-building"></i>
                    <span>Branches</span>
                </a>
            </li>
            @endcan

            @can('user.view')
            <li class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <a href="{{ route('users.index') }}" class="nav-link-item">
                    <i class="bi bi-person-badge-fill"></i>
                    <span>Users</span>
                </a>
            </li>
            @endcan

            @can('notification.view')
            <li class="nav-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                <a href="{{ route('notifications.index') }}" class="nav-link-item">
                    <i class="bi bi-bell-fill"></i>
                    <span>Notifications</span>
                </a>
            </li>
            @endcan

            @can('audit.view')
            <li class="nav-item {{ request()->routeIs('audit.*') ? 'active' : '' }}">
                <a href="{{ route('audit.index') }}" class="nav-link-item">
                    <i class="bi bi-shield-check"></i>
                    <span>Audit Logs</span>
                </a>
            </li>
            @endcan

            @can('settings.view')
            <li class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <a href="{{ route('settings.index') }}" class="nav-link-item">
                    <i class="bi bi-gear-fill"></i>
                    <span>Settings</span>
                </a>
            </li>
            @endcan

        </ul>
    </nav>

    {{-- Sidebar footer --}}
    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">
                <i class="bi bi-box-arrow-left"></i>
                <span>Sign Out</span>
            </button>
        </form>
    </div>
</div>

{{-- Overlay for mobile --}}
<div class="sidebar-overlay" id="sidebarOverlay"></div>

{{-- ─── MAIN CONTENT ────────────────────────────────────────────────── --}}
<div class="lms-main" id="mainContent">

    {{-- Top Navigation Bar --}}
    <header class="lms-topbar">
        <div class="topbar-left">
            <button class="btn btn-link topbar-menu-btn" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <nav aria-label="breadcrumb" class="d-none d-md-block">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    @yield('breadcrumb')
                </ol>
            </nav>
        </div>
        <div class="topbar-right">
            {{-- Branch indicator --}}
            <div class="branch-badge d-none d-sm-flex">
                <i class="bi bi-building me-1"></i>
                <span>{{ auth()->user()->branch?->name ?? 'All Branches' }}</span>
            </div>

            {{-- Notifications bell --}}
            <div class="topbar-icon-btn">
                <i class="bi bi-bell"></i>
                <span class="notif-dot"></span>
            </div>

            {{-- Profile dropdown --}}
            <div class="dropdown">
                <button class="topbar-profile-btn dropdown-toggle" data-bs-toggle="dropdown">
                    <img src="{{ auth()->user()->avatar_url ?? asset('images/default-avatar.png') }}" alt="Profile">
                    <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end lms-dropdown">
                    <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="bi bi-person me-2"></i>My Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-left me-2"></i>Sign Out
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    {{-- Page Content --}}
    <main class="lms-content">
        {{-- Flash messages --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show lms-alert" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show lms-alert" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show lms-alert" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @yield('content')
    </main>

    <footer class="lms-footer">
        <span>© {{ date('Y') }} Finance ERP &mdash; Loan Management System</span>
        <span>v1.0.0</span>
    </footer>
</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- Custom JS --}}
<script src="{{ asset('js/app.js') }}"></script>

@stack('scripts')
</body>
</html>
