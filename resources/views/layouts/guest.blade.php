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
    <main class="flex-1 flex items-center justify-center px-4">
        @yield('content')
    </main>

    <footer class="py-4">
        <div class="text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} ClockWise
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
