@extends('layouts.guest')

@section('title', 'Forgot Password')

@section('content')
<div class="w-full max-w-md bg-white shadow-xl rounded-2xl p-8 auth-card">
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold">🕐 ClockWise</h1>
        <p class="text-sm text-slate-500 mt-1">Reset your password</p>
    </div>

    <p class="text-gray-600 text-sm mb-6">No worries! Enter your email and we'll send you a reset link.</p>

    @if (session('status'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-green-700 text-sm">{{ session('status') }}</span>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700 flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                   class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-slate-400 focus:outline-none"
                   placeholder="your@email.com">
        </div>

        <button type="submit" class="btn-primary w-full rounded-lg px-4 py-2.5 font-medium relative">
            <span class="btn-text">Send Reset Link</span>
            <span class="btn-loader" aria-hidden="true">
                <span class="spinner"></span>
            </span>
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:underline inline-flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Login
        </a>
    </p>
</div>
@endsection
