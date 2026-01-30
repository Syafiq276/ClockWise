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
    <header class="border-b bg-white sticky top-0 z-50">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <div class="flex items-center gap-6">
                <a href="{{ route('dashboard') }}" class="text-lg font-semibold">🕐 ClockWise</a>
                <nav class="hidden md:flex items-center gap-4 text-sm">
                    <a href="{{ route('dashboard') }}" class="hover:text-slate-600 {{ request()->routeIs('dashboard') ? 'font-medium text-slate-900' : 'text-slate-500' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('attendance.history') }}" class="hover:text-slate-600 {{ request()->routeIs('attendance.history') ? 'font-medium text-slate-900' : 'text-slate-500' }}">
                        My Attendance
                    </a>
                    <a href="{{ route('leave.index') }}" class="hover:text-slate-600 {{ request()->routeIs('leave.*') ? 'font-medium text-slate-900' : 'text-slate-500' }}">
                        Leave Requests
                    </a>
                    <a href="{{ route('payslips.index') }}" class="hover:text-slate-600 {{ request()->routeIs('payslips.*') ? 'font-medium text-slate-900' : 'text-slate-500' }}">
                        Payslips
                    </a>
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-600 {{ request()->routeIs('admin.dashboard') ? 'font-medium text-slate-900' : 'text-slate-500' }}">
                        Admin Panel
                    </a>
                    <a href="{{ route('admin.employees') }}" class="hover:text-slate-600 {{ request()->routeIs('admin.employees*') ? 'font-medium text-slate-900' : 'text-slate-500' }}">
                        Employees
                    </a>
                    <a href="{{ route('admin.attendance') }}" class="hover:text-slate-600 {{ request()->routeIs('admin.attendance') ? 'font-medium text-slate-900' : 'text-slate-500' }}">
                        Attendance Log
                    </a>
                    <a href="{{ route('admin.leave') }}" class="hover:text-slate-600 {{ request()->routeIs('admin.leave') ? 'font-medium text-slate-900' : 'text-slate-500' }}">
                        Leave Mgmt
                    </a>
                    <a href="{{ route('admin.reports') }}" class="hover:text-slate-600 {{ request()->routeIs('admin.reports') ? 'font-medium text-slate-900' : 'text-slate-500' }}">
                        Reports
                    </a>
                    <a href="{{ route('admin.payroll.index') }}" class="hover:text-slate-600 {{ request()->routeIs('admin.payroll.*') ? 'font-medium text-slate-900' : 'text-slate-500' }}">
                        Payroll
                    </a>
                    <a href="{{ route('admin.audit-logs') }}" class="hover:text-slate-600 {{ request()->routeIs('admin.audit-logs') ? 'font-medium text-slate-900' : 'text-slate-500' }}">
                        🔐 Audit
                    </a>
                    @endif
                </nav>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-slate-500 hidden sm:inline">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn-secondary rounded-lg px-3 py-1.5 text-sm ripple">
                        Log out
                    </button>
                </form>
            </div>
        </div>
    </header>
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

    <main class="flex-1 fade-in">
        @yield('content')
    </main>

    <footer class="border-t bg-white py-4 mt-auto">
        <div class="mx-auto max-w-7xl px-6 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} ClockWise. Built for SME Malaysia.
        </div>
    </footer>

    <script>
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
