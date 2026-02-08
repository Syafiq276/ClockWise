@extends('layouts.guest')

@section('title', 'Reset Password')

@section('content')
<div class="w-full max-w-md bg-white shadow-xl rounded-2xl p-8 auth-card">
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold">🕐 ClockWise</h1>
        <p class="text-sm text-slate-500 mt-1">Create a new password</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700 flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="block text-sm font-medium">Email Address</label>
            <input type="email" name="email" id="email" value="{{ old('email', $email) }}" required readonly
                   class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 bg-gray-50 text-gray-500">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium">New Password</label>
            <div class="relative mt-1">
                <input type="password" name="password" id="reset_password" required
                       class="w-full rounded-lg border border-slate-200 px-3 py-2 pr-20 focus:border-slate-400 focus:outline-none"
                       placeholder="••••••••">
                <button type="button" id="reset_toggle"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-500 hover:text-slate-700">
                    Show
                </button>
            </div>
            <p class="mt-1 text-xs text-gray-500">Min 8 characters</p>
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium">Confirm New Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required
                   class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-slate-400 focus:outline-none"
                   placeholder="••••••••">
        </div>

        <button type="submit" class="btn-primary w-full rounded-lg px-4 py-2.5 font-medium relative">
            <span class="btn-text">Reset Password</span>
            <span class="btn-loader" aria-hidden="true">
                <span class="spinner"></span>
            </span>
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:underline">Back to Login</a>
    </p>
</div>

@push('scripts')
<script>
    const resetPassword = document.getElementById('reset_password');
    const resetToggle = document.getElementById('reset_toggle');
    resetToggle.addEventListener('click', () => {
        const isHidden = resetPassword.type === 'password';
        resetPassword.type = isHidden ? 'text' : 'password';
        resetToggle.textContent = isHidden ? 'Hide' : 'Show';
    });
</script>
@endpush
@endsection
