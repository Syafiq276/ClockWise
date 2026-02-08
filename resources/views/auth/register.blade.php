@extends('layouts.guest')

@section('title', 'Sign Up - ClockWise')

@section('content')
<div class="w-full max-w-md bg-white shadow-xl rounded-2xl p-8 auth-card">
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold">🕐 ClockWise</h1>
        <p class="text-sm text-slate-500 mt-1">Create your account</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium">Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                   class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-slate-400 focus:outline-none" />
        </div>

        <div>
            <label class="block text-sm font-medium">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-slate-400 focus:outline-none" />
        </div>

        <div>
            <label class="block text-sm font-medium">Password</label>
            <div class="relative mt-1">
                <input id="register_password" type="password" name="password" required
                       class="w-full rounded-lg border border-slate-200 px-3 py-2 pr-20 focus:border-slate-400 focus:outline-none" />
                <button type="button" id="register_toggle"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-500 hover:text-slate-700">
                    Show
                </button>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium">Confirm Password</label>
            <div class="relative mt-1">
                <input id="register_password_confirm" type="password" name="password_confirmation" required
                       class="w-full rounded-lg border border-slate-200 px-3 py-2 pr-20 focus:border-slate-400 focus:outline-none" />
                <button type="button" id="register_toggle_confirm"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-500 hover:text-slate-700">
                    Show
                </button>
            </div>
        </div>

        <button type="submit" class="btn-primary w-full rounded-lg px-4 py-2.5 font-medium relative">
            <span class="btn-text">Create account</span>
            <span class="btn-loader" aria-hidden="true">
                <span class="spinner"></span>
            </span>
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        Already have an account?
        <a href="{{ route('login') }}" class="font-semibold text-slate-900 hover:underline">Sign in</a>
    </p>
</div>
@endsection

@push('scripts')
<script>
    const registerPassword = document.getElementById('register_password');
    const registerToggle = document.getElementById('register_toggle');
    const registerPasswordConfirm = document.getElementById('register_password_confirm');
    const registerToggleConfirm = document.getElementById('register_toggle_confirm');

    registerToggle.addEventListener('click', () => {
        const isHidden = registerPassword.type === 'password';
        registerPassword.type = isHidden ? 'text' : 'password';
        registerToggle.textContent = isHidden ? 'Hide' : 'Show';
    });

    registerToggleConfirm.addEventListener('click', () => {
        const isHidden = registerPasswordConfirm.type === 'password';
        registerPasswordConfirm.type = isHidden ? 'text' : 'password';
        registerToggleConfirm.textContent = isHidden ? 'Hide' : 'Show';
    });
</script>
@endpush
