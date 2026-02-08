@extends('layouts.app')

@section('title', 'Dashboard - ClockWise')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 py-6 sm:py-10">
    <div class="mb-6 rounded-lg border p-3 sm:p-4 shadow-sm {{ $location_type === 'office' ? 'bg-green-100 border-green-400 text-green-800' : 'bg-yellow-100 border-yellow-400 text-yellow-800' }}">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
            <div class="text-sm sm:text-base">
                <span class="font-bold">Connection Status:</span>
                {{ $location_type === 'office' ? 'CONNECTED TO OFFICE NETWORK 🏢' : 'REMOTE / WFH 🏠' }}
            </div>
            <div class="text-xs font-mono">
                Your IP: {{ $current_ip }}
            </div>
        </div>
    </div>

    <div class="rounded-lg bg-white p-6 sm:p-10 text-center shadow-sm">
        @if(!$attendance)
            <form action="{{ route('clock.in') }}" method="POST">
                @csrf
                <button class="bg-blue-600 hover:bg-blue-500 text-white text-xl sm:text-2xl font-bold py-4 px-8 sm:py-6 sm:px-12 rounded-full shadow-lg transform transition hover:scale-105 w-full sm:w-auto">
                    📍 CLOCK IN
                </button>
            </form>
            <p class="mt-4 text-gray-500">Shift starts at 9:00 AM</p>

        @elseif(!$attendance->clock_out)
            <div class="mb-4 text-lg sm:text-xl font-bold text-green-500">
                You clocked in at {{ \Carbon\Carbon::parse($attendance->clock_in)->format('h:i A') }}
            </div>
            <form action="{{ route('clock.out') }}" method="POST">
                @csrf
                <button class="bg-red-600 hover:bg-red-500 text-white text-xl sm:text-2xl font-bold py-4 px-8 sm:py-6 sm:px-12 rounded-full shadow-lg transform transition hover:scale-105 w-full sm:w-auto">
                    🛑 CLOCK OUT
                </button>
            </form>

        @else
            <div class="text-gray-500 text-lg sm:text-xl">
                You have completed your shift today.<br>
                Duration: {{ \Carbon\Carbon::parse($attendance->clock_in)->diffInHours($attendance->clock_out) }} Hours.
            </div>
        @endif
    </div>

    @if(auth()->user()->role === 'admin')
    <div class="mt-6 sm:mt-10 rounded-lg border border-slate-300 bg-slate-100 p-4 sm:p-6">
        <h3 class="mb-2 text-lg font-bold">MD Control Panel</h3>
        <p class="mb-4 text-sm break-all">Current Registered Office IP: <span class="font-mono bg-white px-2 py-1">{{ $office_ip ?? 'Not Set' }}</span></p>

        <form action="{{ route('admin.set-ip') }}" method="POST">
            @csrf
            <button class="rounded bg-gray-800 px-4 py-2 text-sm text-white hover:bg-gray-700 w-full sm:w-auto">
                🔄 Update Office Location to My Current IP
            </button>
        </form>
        <p class="mt-2 text-xs text-gray-500">Click this button when you are physically at the office to reset the Intranet lock.</p>
    </div>
    @endif
</div>
@endsection