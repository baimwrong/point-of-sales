<!DOCTYPE html>
<html lang="id" x-data="appLayout()" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') &mdash; POS System</title>

    {{-- Bootstrap CSS --}}
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    {{-- SweetAlert2 CSS --}}
    <link rel="stylesheet" href="{{ asset('vendor/sweetalert2/sweetalert2.min.css') }}">
    {{-- Select2 CSS --}}
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">

    @yield('styles')
    @stack('styles')

    <style>
        /* =============================================
           CSS VARIABLES & ROOT
        ============================================= */
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 72px;
            --sidebar-bg: #1e293b;
            --sidebar-hover: #334155;
            --sidebar-active: #2563eb;
            --sidebar-text: #94a3b8;
            --sidebar-text-active: #f8fafc;
            --topbar-height: 64px;
            --primary: #2563eb;
            --accent: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --purple: #8b5cf6;
            --body-bg: #f1f5f9;
            --card-bg: #ffffff;
            --transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* =============================================
           BASE
        ============================================= */
        * { box-sizing: border-box; }

        body {
            background-color: var(--body-bg);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            font-size: 0.9rem;
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* =============================================
           SIDEBAR
        ============================================= */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            transition: var(--transition);
            z-index: 1040;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .sidebar-collapsed .sidebar {
            width: var(--sidebar-collapsed-width);
        }

        /* Sidebar Logo */
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 20px;
            height: var(--topbar-height);
            background: rgba(255,255,255,0.04);
            border-bottom: 1px solid rgba(255,255,255,0.06);
            text-decoration: none;
            flex-shrink: 0;
            overflow: hidden;
            white-space: nowrap;
        }

        .sidebar-brand-icon {
            width: 36px;
            height: 36px;
            background: var(--primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
        }

        .sidebar-brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .sidebar-brand-text strong {
            color: #f8fafc;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.01em;
        }

        .sidebar-brand-text small {
            color: var(--sidebar-text);
            font-size: 0.7rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .sidebar-collapsed .sidebar-brand-text,
        .sidebar-collapsed .sidebar-label,
        .sidebar-collapsed .sidebar-section-title,
        .sidebar-collapsed .sidebar-badge {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }

        /* Sidebar Scroll */
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 12px 0;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.15) transparent;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.15);
            border-radius: 4px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.25);
        }

        /* Section Title */
        .sidebar-section-title {
            padding: 8px 20px 4px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #475569;
            white-space: nowrap;
            transition: var(--transition);
        }

        /* Nav Item */
        .sidebar-nav .nav-item {
            padding: 2px 10px;
        }

        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 10px;
            color: var(--sidebar-text);
            text-decoration: none;
            transition: var(--transition);
            white-space: nowrap;
            overflow: hidden;
            position: relative;
        }

        .sidebar-nav .nav-link:hover {
            background: var(--sidebar-hover);
            color: var(--sidebar-text-active);
        }

        .sidebar-nav .nav-link.active {
            background: var(--primary);
            color: var(--sidebar-text-active);
            box-shadow: 0 4px 16px rgba(37, 99, 235, 0.35);
        }

        .sidebar-nav .nav-link.active-accent {
            background: linear-gradient(135deg, var(--accent), #059669);
            color: #fff;
            box-shadow: 0 4px 16px rgba(16, 185, 129, 0.35);
        }

        .sidebar-nav .nav-icon {
            width: 20px;
            text-align: center;
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        .sidebar-label {
            flex: 1;
            font-size: 0.875rem;
            font-weight: 500;
            transition: var(--transition);
        }

        .sidebar-badge {
            font-size: 0.65rem;
            padding: 2px 7px;
            border-radius: 20px;
            background: var(--warning);
            color: #000;
            font-weight: 700;
            flex-shrink: 0;
            transition: var(--transition);
        }

        /* Sidebar Footer */
        .sidebar-footer {
            padding: 12px 10px;
            border-top: 1px solid rgba(255,255,255,0.06);
            flex-shrink: 0;
            overflow: hidden;
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            background: rgba(255,255,255,0.04);
            cursor: default;
            white-space: nowrap;
            overflow: hidden;
        }

        .sidebar-user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            color: #fff;
            font-weight: 700;
            flex-shrink: 0;
        }

        .sidebar-user-info strong {
            display: block;
            color: #f8fafc;
            font-size: 0.82rem;
            font-weight: 600;
        }

        .sidebar-user-info small {
            color: var(--sidebar-text);
            font-size: 0.7rem;
        }

        /* =============================================
           TOPBAR
        ============================================= */
        .topbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--topbar-height);
            background: var(--card-bg);
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 24px;
            border-bottom: 1px solid #e2e8f0;
            z-index: 1030;
            transition: var(--transition);
            box-shadow: 0 1px 8px rgba(0,0,0,0.05);
        }

        .sidebar-collapsed .topbar {
            left: var(--sidebar-collapsed-width);
        }

        .topbar-toggle {
            width: 36px;
            height: 36px;
            border: none;
            background: transparent;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            cursor: pointer;
            transition: var(--transition);
            font-size: 1.1rem;
        }

        .topbar-toggle:hover {
            background: #f1f5f9;
            color: var(--primary);
        }

        .topbar-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #1e293b;
            flex: 1;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .topbar-btn {
            width: 38px;
            height: 38px;
            border: none;
            background: transparent;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            cursor: pointer;
            transition: var(--transition);
            font-size: 1rem;
            position: relative;
            text-decoration: none;
        }

        .topbar-btn:hover {
            background: #f1f5f9;
            color: var(--primary);
        }

        .topbar-btn .badge-dot {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 8px;
            height: 8px;
            background: var(--danger);
            border-radius: 50%;
            border: 2px solid #fff;
        }

        .topbar-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), #1d4ed8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            border: 2px solid #e2e8f0;
            transition: var(--transition);
        }

        .topbar-avatar:hover {
            border-color: var(--primary);
        }

        /* User Dropdown */
        .user-dropdown-menu {
            min-width: 220px;
            border: none;
            box-shadow: 0 16px 48px rgba(0,0,0,0.12);
            border-radius: 14px;
            padding: 8px;
            margin-top: 8px !important;
        }

        .user-dropdown-header {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            margin-bottom: 4px;
        }

        .user-dropdown-menu .dropdown-item {
            border-radius: 8px;
            padding: 9px 14px;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #374151;
            transition: var(--transition);
        }

        .user-dropdown-menu .dropdown-item:hover {
            background: #f1f5f9;
            color: var(--primary);
        }

        .user-dropdown-menu .dropdown-item.text-danger:hover {
            background: #fef2f2;
            color: var(--danger);
        }

        /* =============================================
           MAIN CONTENT
        ============================================= */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            padding-top: var(--topbar-height);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: var(--transition);
        }

        .sidebar-collapsed .main-wrapper {
            margin-left: var(--sidebar-collapsed-width);
        }

        .main-content {
            flex: 1;
            padding: 28px 28px 16px;
        }

        /* =============================================
           FOOTER
        ============================================= */
        .main-footer {
            padding: 14px 28px;
            border-top: 1px solid #e2e8f0;
            background: #fff;
            color: #94a3b8;
            font-size: 0.78rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* =============================================
           OVERLAY (mobile)
        ============================================= */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1039;
            backdrop-filter: blur(2px);
        }

        /* =============================================
           CARDS / COMMON
        ============================================= */
        .card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 1px 8px rgba(0,0,0,0.06);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid #f1f5f9;
            padding: 16px 20px;
            font-weight: 600;
            color: #1e293b;
        }

        .btn {
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .table th {
            font-weight: 600;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
            border-bottom: 2px solid #f1f5f9;
        }

        .badge {
            font-weight: 600;
            font-size: 0.72rem;
        }

        /* Select2 Bootstrap 5 compatibility */
        .select2-container--default .select2-selection--single {
            height: calc(2.25rem + 2px);
            padding: 0.375rem 0.75rem;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            top: 50%;
            transform: translateY(-50%);
        }

        /* =============================================
           RESPONSIVE
        ============================================= */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-width) !important;
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            .sidebar-overlay.active {
                display: block;
            }

            .topbar {
                left: 0 !important;
            }

            .main-wrapper {
                margin-left: 0 !important;
            }

            .main-content {
                padding: 20px 16px;
            }

            .main-footer {
                padding: 12px 16px;
            }
        }

        /* =============================================
           ANIMATIONS
        ============================================= */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-up {
            animation: fadeInUp 0.4s ease both;
        }

        .animate-delay-1 { animation-delay: 0.05s; }
        .animate-delay-2 { animation-delay: 0.10s; }
        .animate-delay-3 { animation-delay: 0.15s; }
        .animate-delay-4 { animation-delay: 0.20s; }
    </style>
</head>

<body>

{{-- =============================================
     SIDEBAR OVERLAY (mobile)
============================================= --}}
<div class="sidebar-overlay" :class="{ 'active': mobileOpen }" @click="closeMobile()"></div>

{{-- =============================================
     SIDEBAR
============================================= --}}
<aside class="sidebar" :class="{ 'mobile-open': mobileOpen }">

    {{-- Brand / Logo --}}
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <i class="fas fa-cash-register"></i>
        </div>
        <div class="sidebar-brand-text">
            <strong>POS System</strong>
            <small>Point of Sales</small>
        </div>
    </a>

    {{-- Navigation --}}
    <nav class="sidebar-nav" id="sidebarNav">

        {{-- Main --}}
        <div class="sidebar-section-title">Main</div>

        <div class="nav-item">
            <a href="{{ route('dashboard') }}"
               class="nav-link {{ Route::is('dashboard') ? 'active' : '' }}">
                <span class="sidebar-icon nav-icon"><i class="fas fa-gauge-high"></i></span>
                <span class="sidebar-label">Dashboard</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="{{ route('pos.index') }}"
               class="nav-link {{ Route::is('pos.*') ? 'active-accent' : '' }}">
                <span class="sidebar-icon nav-icon"><i class="fas fa-cash-register"></i></span>
                <span class="sidebar-label">POS Terminal</span>
            </a>
        </div>

        {{-- Sales & Finance --}}
        <div class="sidebar-section-title mt-2">Sales &amp; Finance</div>

        <div class="nav-item">
            <a href="{{ route('sales.index') }}"
               class="nav-link {{ Route::is('sales.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fas fa-receipt"></i></span>
                <span class="sidebar-label">Sales</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="{{ route('purchases.index') }}"
               class="nav-link {{ Route::is('purchases.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fas fa-cart-arrow-down"></i></span>
                <span class="sidebar-label">Purchases</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="{{ route('returns.index') }}"
               class="nav-link {{ Route::is('returns.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fas fa-rotate-left"></i></span>
                <span class="sidebar-label">Returns</span>
            </a>
        </div>

        {{-- Inventory --}}
        <div class="sidebar-section-title mt-2">Inventory</div>

        <div class="nav-item">
            <a href="{{ route('products.index') }}"
               class="nav-link {{ Route::is('products.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fas fa-boxes-stacked"></i></span>
                <span class="sidebar-label">Products</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="{{ route('categories.index') }}"
               class="nav-link {{ Route::is('categories.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fas fa-tags"></i></span>
                <span class="sidebar-label">Categories</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="{{ route('suppliers.index') }}"
               class="nav-link {{ Route::is('suppliers.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fas fa-truck"></i></span>
                <span class="sidebar-label">Suppliers</span>
            </a>
        </div>

        {{-- Relations --}}
        <div class="sidebar-section-title mt-2">Relations</div>

        <div class="nav-item">
            <a href="{{ route('customers.index') }}"
               class="nav-link {{ Route::is('customers.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fas fa-users"></i></span>
                <span class="sidebar-label">Customers</span>
            </a>
        </div>

        {{-- Reports --}}
        <div class="sidebar-section-title mt-2">Analytics</div>

        <div class="nav-item">
            <a href="{{ route('reports.index') }}"
               class="nav-link {{ Route::is('reports.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fas fa-chart-bar"></i></span>
                <span class="sidebar-label">Reports</span>
            </a>
        </div>

        {{-- Admin Only --}}
        @if(auth()->check() && auth()->user()->isAdmin())
            <div class="sidebar-section-title mt-2">Administration</div>

            <div class="nav-item">
                <a href="{{ route('users.index') }}"
                   class="nav-link {{ Route::is('users.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="fas fa-user-gear"></i></span>
                    <span class="sidebar-label">Users</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('settings.index') }}"
                   class="nav-link {{ Route::is('settings.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="fas fa-gear"></i></span>
                    <span class="sidebar-label">Settings</span>
                </a>
            </div>
        @endif

    </nav>

    {{-- Sidebar User Footer --}}
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>
            <div class="sidebar-user-info">
                <strong>{{ Str::limit(auth()->user()->name ?? 'User', 20) }}</strong>
                <small>{{ auth()->user()->role?->display_name ?? 'Staff' }}</small>
            </div>
        </div>
    </div>
</aside>

{{-- =============================================
     MAIN WRAPPER
============================================= --}}
<div class="main-wrapper">

    {{-- TOPBAR --}}
    <header class="topbar">
        {{-- Hamburger --}}
        <button class="topbar-toggle"
                @click="toggleSidebar()"
                title="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </button>

        {{-- Page Title --}}
        <div class="topbar-title d-none d-md-block">
            @yield('title', 'Dashboard')
        </div>

        <div class="flex-fill"></div>

        {{-- Topbar Actions --}}
        <div class="topbar-actions">

            {{-- Notifications --}}
            <div class="dropdown">
                <button class="topbar-btn" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="badge-dot"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end user-dropdown-menu" style="min-width:300px">
                    <li class="user-dropdown-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <strong style="font-size:.9rem;color:#1e293b">Notifications</strong>
                            <span class="badge bg-primary">3</span>
                        </div>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="#">
                            <i class="fas fa-triangle-exclamation text-warning"></i>
                            <div>
                                <div style="font-size:.82rem;font-weight:600">Low stock alert</div>
                                <div style="font-size:.75rem;color:#94a3b8">5 products below minimum</div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="#">
                            <i class="fas fa-receipt text-success"></i>
                            <div>
                                <div style="font-size:.82rem;font-weight:600">New sale completed</div>
                                <div style="font-size:.75rem;color:#94a3b8">Invoice #INV-00123</div>
                            </div>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item text-center" href="#" style="font-size:.82rem;color:var(--primary)">
                            View all notifications
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Fullscreen --}}
            <button class="topbar-btn d-none d-md-flex" onclick="toggleFullscreen()" title="Fullscreen">
                <i class="fas fa-expand" id="fullscreenIcon"></i>
            </button>

            {{-- User Dropdown --}}
            <div class="dropdown">
                <div class="topbar-avatar" data-bs-toggle="dropdown" aria-expanded="false" title="Account">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </div>
                <ul class="dropdown-menu dropdown-menu-end user-dropdown-menu">
                    <li>
                        <div class="user-dropdown-header">
                            <div style="font-weight:700;color:#1e293b;font-size:.9rem">
                                {{ auth()->user()->name ?? 'User' }}
                            </div>
                            <div style="font-size:.78rem;color:#94a3b8">
                                {{ auth()->user()->email ?? '' }}
                            </div>
                            <span class="badge mt-1"
                                  style="background:rgba(37,99,235,.12);color:var(--primary);font-size:.68rem">
                                {{ auth()->user()->role?->display_name ?? 'Staff' }}
                            </span>
                        </div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="fas fa-user-circle text-primary"></i>
                            My Profile
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('settings.index') }}">
                            <i class="fas fa-gear text-secondary"></i>
                            Settings
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-right-from-bracket"></i>
                                Sign Out
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    {{-- MAIN CONTENT --}}
    <main class="main-content">
        {{-- Flash Messages --}}
        <x-alert />

        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="main-footer">
        <span>&copy; {{ date('Y') }} <strong>POS System</strong>. All rights reserved.</span>
        <span>Version <strong>1.0.0</strong></span>
    </footer>

</div>{{-- /main-wrapper --}}

{{-- =============================================
     SCRIPTS
============================================= --}}
{{-- Bootstrap JS --}}
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
{{-- Alpine.js (defer) --}}
<script defer src="{{ asset('vendor/alpinejs/alpine.min.js') }}"></script>
{{-- Chart.js --}}
<script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
{{-- SweetAlert2 --}}
<script src="{{ asset('vendor/sweetalert2/sweetalert2.min.js') }}"></script>
{{-- jQuery (for Select2) --}}
<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
{{-- Select2 --}}
<script src="{{ asset('vendor/select2/js/select2.min.js') }}"></script>

<script>
    /* =============================================
       APP LAYOUT ALPINE COMPONENT
    ============================================= */
    function appLayout() {
        return {
            sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
            mobileOpen: false,

            toggleSidebar() {
                if (window.innerWidth <= 991) {
                    this.mobileOpen = !this.mobileOpen;
                } else {
                    this.sidebarCollapsed = !this.sidebarCollapsed;
                    localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
                }
            },

            closeMobile() {
                this.mobileOpen = false;
            }
        };
    }

    /* =============================================
       FULLSCREEN TOGGLE
    ============================================= */
    function toggleFullscreen() {
        const icon = document.getElementById('fullscreenIcon');
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
            icon.classList.replace('fa-expand', 'fa-compress');
        } else {
            document.exitFullscreen();
            icon.classList.replace('fa-compress', 'fa-expand');
        }
    }

    /* =============================================
       FLASH MESSAGES VIA SWEETALERT2
    ============================================= */
    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: @json(session('success')),
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                customClass: { popup: 'rounded-3 shadow' }
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: @json(session('error')),
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                customClass: { popup: 'rounded-3 shadow' }
            });
        @endif

        @if(session('warning'))
            Swal.fire({
                icon: 'warning',
                title: 'Warning!',
                text: @json(session('warning')),
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4500,
                timerProgressBar: true,
                customClass: { popup: 'rounded-3 shadow' }
            });
        @endif

        @if(session('info'))
            Swal.fire({
                icon: 'info',
                title: 'Info',
                text: @json(session('info')),
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                customClass: { popup: 'rounded-3 shadow' }
            });
        @endif

        /* Select2 default init */
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2').select2({ theme: 'classic', width: '100%' });
        }
    });

    /* =============================================
       GLOBAL DELETE CONFIRMATION
    ============================================= */
    function confirmDelete(formId, itemName) {
        Swal.fire({
            title: 'Are you sure?',
            text: `Delete "${itemName}"? This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById(formId);
                if (form) form.submit();
            }
        });
    }

    // Also support declarative [data-confirm-delete]
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-confirm-delete]');
        if (!btn) return;
        e.preventDefault();
        const formId = btn.getAttribute('data-form-id');
        const itemName = btn.getAttribute('data-item-name') || 'this item';
        confirmDelete(formId, itemName);
    });
</script>

@yield('scripts')
@stack('scripts')

</body>
</html>
