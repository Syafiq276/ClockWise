@extends('layouts.guest')

@section('title', 'Login - ClockWise')

@section('content')
<div class="w-full max-w-md bg-white shadow-xl rounded-2xl p-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Welcome back</h1>
        <p class="text-sm text-slate-500">Sign in to continue.</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-slate-400 focus:outline-none" />
        </div>

        <div>
            <label class="block text-sm font-medium">Password</label>
            <div class="relative mt-1">
                <input id="login_password" type="password" name="password" required
                       class="w-full rounded-lg border border-slate-200 px-3 py-2 pr-20 focus:border-slate-400 focus:outline-none" />
                <button type="button" id="login_toggle"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-500 hover:text-slate-700">
                    Show
                </button>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" class="rounded border-slate-300" />
                Remember me
            </label>
            <a href="#" class="text-sm text-slate-500 hover:text-slate-700">Forgot password?</a>
        </div>

        <button type="submit" class="w-full rounded-lg bg-slate-900 px-4 py-2 text-white hover:bg-slate-800">
            Sign in
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        Don't have an account?
        <a href="{{ route('register') }}" class="font-semibold text-slate-900 hover:underline">Sign up</a>
    </p>
</div>
@endsection

@push('scripts')
<script>
    const loginPassword = document.getElementById('login_password');
    const loginToggle = document.getElementById('login_toggle');

    loginToggle.addEventListener('click', () => {
        const isHidden = loginPassword.type === 'password';
        loginPassword.type = isHidden ? 'text' : 'password';
        loginToggle.textContent = isHidden ? 'Hide' : 'Show';
    });
</script>
@endpush
