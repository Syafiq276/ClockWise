@extends('layouts.app')

@section('title', 'My Payslips')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">My Payslips</h1>
        <p class="text-gray-600">View your salary history and payslips</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-4">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total Earned (YTD)</p>
                    <p class="text-xl font-bold text-green-600">RM {{ number_format($totalEarned, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Payslips</p>
                    <p class="text-xl font-bold text-blue-600">{{ $payslips->total() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total Hours (YTD)</p>
                    <p class="text-xl font-bold text-purple-600">{{ number_format($totalHours, 1) }}h</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Payslips List -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        @if($payslips->count() > 0)
            <div class="divide-y divide-gray-200">
                @foreach($payslips as $payslip)
                    <div class="block p-4 hover:bg-gray-50 transition table-row-hover">
                        <div class="flex items-center justify-between">
                            <a href="{{ route('payslips.show', $payslip) }}" class="flex items-center flex-1">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center text-white">
                                    <span class="text-sm font-bold">
                                        {{ \Carbon\Carbon::parse($payslip->month_year . '-01')->format('M') }}
                                    </span>
                                </div>
                                <div class="ml-4">
                                    <p class="font-semibold text-gray-800">
                                        {{ \Carbon\Carbon::parse($payslip->month_year . '-01')->format('F Y') }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ $payslip->days_worked }} days · {{ $payslip->total_hours }} hours
                                        @if($payslip->overtime_hours > 0)
                                            <span class="text-purple-600">(+{{ $payslip->overtime_hours }}h OT)</span>
                                        @endif
                                    </p>
                                </div>
                            </a>
                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <p class="text-lg font-bold text-gray-800">RM {{ number_format($payslip->net_pay, 2) }}</p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $payslip->getStatusBadgeClass() }}">
                                        {{ ucfirst($payslip->status) }}
                                    </span>
                                </div>
                                <a href="{{ route('payslips.pdf', $payslip) }}" 
                                   class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition"
                                   title="Download PDF">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($payslips->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $payslips->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No payslips yet</h3>
                <p class="mt-1 text-sm text-gray-500">Your payslips will appear here once processed.</p>
            </div>
        @endif
    </div>

    <!-- Back Link -->
    <div class="mt-6">
        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 inline-flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Dashboard
        </a>
    </div>
</div>
@endsection
