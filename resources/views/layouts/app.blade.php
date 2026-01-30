<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ClockWise')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* ============================================
           SIDEBAR NAVIGATION STYLES
           ============================================ */
        
        .sidebar {
            position: fixed;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 40;
            transition: all 0.3s ease;
        }
        
        .sidebar-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 50px rgba(0,0,0,0.1), 0 0 0 1px rgba(0,0,0,0.05);
            padding: 16px 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 56px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .sidebar-card:hover {
            min-width: 200px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15), 0 0 0 1px rgba(0,0,0,0.05);
        }
        
        .sidebar-card:hover .nav-label {
            opacity: 1;
            width: auto;
            margin-left: 12px;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            padding: 12px;
            border-radius: 12px;
            color: #64748b;
            transition: all 0.2s ease;
            text-decoration: none;
            white-space: nowrap;
            overflow: hidden;
        }
        
        .nav-item:hover {
            background: #f1f5f9;
            color: #1e293b;
        }
        
        .nav-item.active {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }
        
        .nav-item svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
        
        .nav-label {
            opacity: 0;
            width: 0;
            overflow: hidden;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
        }
        
        .nav-divider {
            height: 1px;
            background: #e2e8f0;
            margin: 8px 4px;
        }
        
        .nav-section-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
            padding: 8px 12px 4px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .sidebar-card:hover .nav-section-label {
            opacity: 1;
        }
        
        /* Mobile sidebar toggle */
        .sidebar-toggle {
            display: none;
            position: fixed;
            bottom: 20px;
            left: 20px;
            z-index: 50;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.4);
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .sidebar-toggle:hover {
            transform: scale(1.05);
        }
        
        .sidebar-toggle:active {
            transform: scale(0.95);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: 0;
                top: auto;
                bottom: 0;
                transform: translateY(100%);
                width: 100%;
                padding: 0;
            }
            
            .sidebar.open {
                transform: translateY(0);
            }
            
            .sidebar-card {
                border-radius: 20px 20px 0 0;
                min-width: 100%;
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: center;
                padding: 16px 12px 24px;
                gap: 8px;
            }
            
            .sidebar-card:hover {
                min-width: 100%;
            }
            
            .nav-item {
                flex-direction: column;
                padding: 10px 16px;
                gap: 4px;
            }
            
            .nav-label {
                opacity: 1;
                width: auto;
                margin-left: 0;
                font-size: 11px;
            }
            
            .sidebar-card:hover .nav-label {
                margin-left: 0;
            }
            
            .nav-divider, .nav-section-label {
                display: none;
            }
            
            .sidebar-toggle {
                display: flex;
            }
            
            .main-content {
                margin-left: 0 !important;
                padding-bottom: 80px;
            }
        }
        
        .main-content {
            margin-left: 90px;
            transition: margin-left 0.3s ease;
        }
        
        /* Sidebar overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.3);
            z-index: 35;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }

        /* ============================================
           UI FEEDBACK & ANIMATIONS
           ============================================ */
        
        /* Page Loading Overlay */
        .page-loading {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s, visibility 0.2s;
        }
        .page-loading.active {
            opacity: 1;
            visibility: visible;
        }
        .page-loading .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #e2e8f0;
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        /* Spinner Animation */
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Button Styles with Loading State */
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            position: relative;
            overflow: hidden;
            transition: all 0.2s ease;
        }
        .btn-primary:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }
        .btn-primary:active:not(:disabled) {
            transform: translateY(0);
        }
        .btn-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        .btn-primary.loading .btn-text {
            visibility: hidden;
        }
        .btn-primary.loading .btn-loader {
            display: block !important;
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
        }

        .btn-secondary {
            background: white;
            border: 1px solid #e2e8f0;
            color: #475569;
            transition: all 0.2s ease;
        }
        .btn-secondary:hover:not(:disabled) {
            background: #f8fafc;
            border-color: #cbd5e1;
        }
        .btn-secondary:active:not(:disabled) {
            background: #f1f5f9;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            transition: all 0.2s ease;
        }
        .btn-danger:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            transition: all 0.2s ease;
        }
        .btn-success:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }

        /* Ripple Effect */
        .ripple {
            position: relative;
            overflow: hidden;
        }
        .ripple::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            background-image: radial-gradient(circle, rgba(255,255,255,0.3) 10%, transparent 10%);
            background-repeat: no-repeat;
            background-position: 50%;
            transform: scale(10, 10);
            opacity: 0;
            transition: transform 0.4s, opacity 0.8s;
        }
        .ripple:active::after {
            transform: scale(0, 0);
            opacity: 0.3;
            transition: 0s;
        }

        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .toast {
            padding: 14px 20px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 300px;
            max-width: 400px;
            animation: slideIn 0.3s ease, fadeOut 0.3s ease 4.7s forwards;
        }
        .toast-success { background: #10b981; color: white; }
        .toast-error { background: #ef4444; color: white; }
        .toast-warning { background: #f59e0b; color: white; }
        .toast-info { background: #3b82f6; color: white; }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes fadeOut {
            to { opacity: 0; transform: translateX(20px); }
        }

        /* Card Hover Effects */
        .card-hover {
            transition: all 0.2s ease;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }

        /* Table Row Hover */
        .table-row-hover {
            transition: background-color 0.15s ease;
        }
        .table-row-hover:hover {
            background-color: #f8fafc;
        }

        /* Input Focus Effects */
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Skeleton Loading */
        .skeleton {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: skeleton 1.5s ease-in-out infinite;
        }
        @keyframes skeleton {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Pulse Animation for Status Indicators */
        .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        .pulse-dot.green { background: #10b981; }
        .pulse-dot.yellow { background: #f59e0b; }
        .pulse-dot.red { background: #ef4444; }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.7; }
        }

        /* Smooth Page Transitions */
        .fade-in {
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Progress Bar */
        .progress-bar {
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
            border-radius: 2px;
            transition: width 0.3s ease;
        }

        /* Mobile Menu Animation */
        .mobile-menu {
            transition: max-height 0.3s ease, opacity 0.3s ease;
            max-height: 0;
            opacity: 0;
            overflow: hidden;
        }
        .mobile-menu.open {
            max-height: 500px;
            opacity: 1;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col">
    <!-- Page Loading Overlay -->
    <div class="page-loading" id="pageLoading">
        <div class="spinner"></div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    @auth
    <!-- Top Header - Profile & Logout only -->
    <header class="border-b bg-white sticky top-0 z-30">
        <div class="mx-auto flex items-center justify-between px-6 py-3 main-content">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                    🕐 ClockWise
                </a>
                @if(auth()->user()->role === 'admin')
                <span class="px-2 py-0.5 text-xs font-medium bg-gradient-to-r from-purple-500 to-indigo-500 text-white rounded-full">Admin</span>
                @endif
            </div>
            <div class="flex items-center gap-4">
                <div class="hidden sm:flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 flex items-center justify-center text-white text-sm font-medium">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="text-sm">
                        <div class="font-medium text-slate-700">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-slate-400">{{ auth()->user()->position ?? 'Employee' }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="flex items-center gap-2 text-slate-500 hover:text-slate-700 text-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="hidden sm:inline">Log out</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Floating Sidebar Navigation -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-card">
            <!-- Employee Section -->
            <span class="nav-section-label">Menu</span>
            
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" title="Dashboard">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="nav-label">Dashboard</span>
            </a>
            
            <a href="{{ route('attendance.history') }}" class="nav-item {{ request()->routeIs('attendance.history') ? 'active' : '' }}" title="My Attendance">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="nav-label">Attendance</span>
            </a>
            
            <a href="{{ route('leave.index') }}" class="nav-item {{ request()->routeIs('leave.*') ? 'active' : '' }}" title="Leave Requests">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="nav-label">Leave</span>
            </a>
            
            <a href="{{ route('payslips.index') }}" class="nav-item {{ request()->routeIs('payslips.*') ? 'active' : '' }}" title="Payslips">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="nav-label">Payslips</span>
            </a>

            @if(auth()->user()->role === 'admin')
            <div class="nav-divider"></div>
            <span class="nav-section-label">Admin</span>
            
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" title="Admin Dashboard">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span class="nav-label">Overview</span>
            </a>
            
            <a href="{{ route('admin.employees') }}" class="nav-item {{ request()->routeIs('admin.employees*') ? 'active' : '' }}" title="Employees">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span class="nav-label">Employees</span>
            </a>
            
            <a href="{{ route('admin.attendance') }}" class="nav-item {{ request()->routeIs('admin.attendance') ? 'active' : '' }}" title="Attendance Log">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span class="nav-label">Attendance</span>
            </a>
            
            <a href="{{ route('admin.leave') }}" class="nav-item {{ request()->routeIs('admin.leave') ? 'active' : '' }}" title="Leave Management">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="nav-label">Leave Mgmt</span>
            </a>
            
            <a href="{{ route('admin.reports') }}" class="nav-item {{ request()->routeIs('admin.reports') ? 'active' : '' }}" title="Reports">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="nav-label">Reports</span>
            </a>
            
            <a href="{{ route('admin.payroll.index') }}" class="nav-item {{ request()->routeIs('admin.payroll.*') ? 'active' : '' }}" title="Payroll">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="nav-label">Payroll</span>
            </a>
            
            <a href="{{ route('admin.audit-logs') }}" class="nav-item {{ request()->routeIs('admin.audit-logs') ? 'active' : '' }}" title="Audit Logs">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span class="nav-label">Audit</span>
            </a>
            @endif
        </div>
    </nav>

    <!-- Mobile Sidebar Toggle -->
    <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>
    @endauth

    {{-- Toast Messages from Session --}}
    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('{{ session('success') }}', 'success');
        });
    </script>
    @endif

    @if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('{{ session('error') }}', 'error');
        });
    </script>
    @endif

    <main class="flex-1 fade-in @auth main-content @endauth">
        @yield('content')
    </main>

    <footer class="border-t bg-white py-4 mt-auto @auth main-content @endauth">
        <div class="mx-auto max-w-7xl px-6 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} ClockWise. Built for SME Malaysia.
        </div>
    </footer>

    <script>
        // ============================================
        // SIDEBAR TOGGLE (Mobile)
        // ============================================
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggle = document.getElementById('sidebarToggle');
            
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
            
            // Change toggle icon
            if (sidebar.classList.contains('open')) {
                toggle.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
            } else {
                toggle.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>';
            }
        }

        // ============================================
        // GLOBAL UI FEEDBACK FUNCTIONS
        // ============================================

        // Toast Notification
        function showToast(message, type = 'info', duration = 5000) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            
            const icons = {
                success: '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                error: '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                warning: '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
                info: '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
            };
            
            toast.innerHTML = `
                ${icons[type] || icons.info}
                <span class="flex-1">${message}</span>
                <button onclick="this.parentElement.remove()" class="ml-2 hover:opacity-70">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            `;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, duration);
        }

        // Page Loading
        function showPageLoading() {
            document.getElementById('pageLoading').classList.add('active');
        }
        
        function hidePageLoading() {
            document.getElementById('pageLoading').classList.remove('active');
        }

        // Button Loading State
        function setButtonLoading(button, loading = true) {
            if (loading) {
                button.classList.add('loading');
                button.disabled = true;
            } else {
                button.classList.remove('loading');
                button.disabled = false;
            }
        }

        // Form Submit with Loading
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-add loading state to forms with btn-primary
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const btn = form.querySelector('button[type="submit"].btn-primary, button[type="submit"].btn-success, button[type="submit"].btn-danger');
                    if (btn && !btn.classList.contains('no-loading')) {
                        setButtonLoading(btn, true);
                    }
                });
            });

            // Add ripple effect to all ripple buttons
            document.querySelectorAll('.ripple').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    const rect = btn.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    btn.style.setProperty('--ripple-x', x + 'px');
                    btn.style.setProperty('--ripple-y', y + 'px');
                });
            });

            // Confirm dialogs with better UX
            document.querySelectorAll('[data-confirm]').forEach(el => {
                el.addEventListener('click', function(e) {
                    if (!confirm(this.dataset.confirm || 'Are you sure?')) {
                        e.preventDefault();
                    }
                });
            });
        });

        // AJAX helper with loading state
        async function fetchWithLoading(url, options = {}) {
            showPageLoading();
            try {
                const response = await fetch(url, {
                    ...options,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        ...options.headers
                    }
                });
                return response;
            } finally {
                hidePageLoading();
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
