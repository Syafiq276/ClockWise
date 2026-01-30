<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ClockWise')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col">
    @auth
    <header class="border-b bg-white sticky top-0 z-50">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <div class="flex items-center gap-6">
                <a href="{{ route('dashboard') }}" class="text-lg font-semibold">ClockWise</a>
                <nav class="hidden md:flex items-center gap-4 text-sm">
                    <a href="{{ route('dashboard') }}" class="hover:text-slate-600 {{ request()->routeIs('dashboard') ? 'font-medium text-slate-900' : 'text-slate-500' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('attendance.history') }}" class="hover:text-slate-600 {{ request()->routeIs('attendance.history') ? 'font-medium text-slate-900' : 'text-slate-500' }}">
                        My Attendance
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
                    @endif
                </nav>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-slate-500 hidden sm:inline">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm hover:bg-slate-100 transition">
                        Log out
                    </button>
                </form>
            </div>
        </div>
    </header>
    @endauth

    @if(session('success'))
    <div class="bg-green-100 border-b border-green-300 text-green-800 px-6 py-3 text-sm">
        <div class="mx-auto max-w-7xl">{{ session('success') }}</div>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border-b border-red-300 text-red-800 px-6 py-3 text-sm">
        <div class="mx-auto max-w-7xl">{{ session('error') }}</div>
    </div>
    @endif

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t bg-white py-4 mt-auto">
        <div class="mx-auto max-w-7xl px-6 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} ClockWise. Built for SME Malaysia.
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
