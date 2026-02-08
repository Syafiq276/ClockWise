<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ClockWise')</title>
    {{-- Tailwind is now built into app.css via Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Button Loading States */
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-primary:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }
        .btn-primary:active:not(:disabled) {
            transform: translateY(0);
        }
        .btn-primary:disabled {
            cursor: not-allowed;
        }
        .btn-primary.loading {
            pointer-events: none;
        }
        .btn-primary.loading .btn-text {
            opacity: 0;
            transform: translateY(4px);
            transition: all 0.2s ease;
        }
        .btn-primary.loading .btn-loader {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }

        /* Spinner */
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(255,255,255,0.4); }
            70% { box-shadow: 0 0 0 6px rgba(255,255,255,0); }
            100% { box-shadow: 0 0 0 0 rgba(255,255,255,0); }
        }
        .btn-loader {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%) scale(0.5);
            opacity: 0;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-loader .spinner {
            width: 22px;
            height: 22px;
            border: 2.5px solid rgba(255,255,255,0.25);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.65s linear infinite;
        }
        .btn-loader .spinner-dot {
            position: absolute;
            top: -1px;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 4px;
            background: white;
            border-radius: 50%;
            animation: pulse-ring 1.2s ease infinite;
        }

        /* Button text transition */
        .btn-text {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        /* Input Focus */
        input:focus {
            outline: none;
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Card animation */
        .auth-card {
            animation: slideUp 0.4s ease;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
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
        .toast-info { background: #3b82f6; color: white; }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes fadeOut {
            to { opacity: 0; transform: translateX(20px); }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col">
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <main class="flex-1 flex items-center justify-center px-4">
        @yield('content')
    </main>

    <footer class="py-4">
        <div class="text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} ClockWise
        </div>
    </footer>

    <script>
        // Toast Notification
        function showToast(message, type = 'info', duration = 5000) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            
            const icons = {
                success: '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                error: '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
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
            setTimeout(() => toast.remove(), duration);
        }

        // Form Loading State
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const btn = form.querySelector('button[type="submit"].btn-primary');
                    if (btn) {
                        btn.classList.add('loading');
                        btn.disabled = true;
                    }
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
