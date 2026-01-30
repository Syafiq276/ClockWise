@extends('layouts.app')

@section('title', 'My Leave Requests')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">My Leave Requests</h1>
            <p class="text-gray-600">View and manage your leave applications</p>
        </div>
        <a href="{{ route('leave.create') }}" 
           class="btn-primary inline-flex items-center px-4 py-2 rounded-lg ripple">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            New Request
        </a>
    </div>

    <!-- Leave Balance Cards -->
    @if(isset($balances))
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <!-- Annual Leave Balance -->
        <div class="bg-white rounded-xl shadow-md p-5 card-hover">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span>
                    Annual Leave
                </h3>
                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded">
                    {{ date('Y') }}
                </span>
            </div>
            <div class="flex items-end gap-4">
                <div class="flex-1">
                    <p class="text-3xl font-bold text-blue-600">{{ $balances['annual']['available'] }}</p>
                    <p class="text-sm text-gray-500">days available</p>
                </div>
                <div class="text-right text-sm">
                    <p class="text-gray-600">Entitlement: <span class="font-medium">{{ $balances['annual']['entitlement'] }}</span></p>
                    <p class="text-green-600">Used: <span class="font-medium">{{ $balances['annual']['used'] }}</span></p>
                    @if($balances['annual']['pending'] > 0)
                    <p class="text-amber-600">Pending: <span class="font-medium">{{ $balances['annual']['pending'] }}</span></p>
                    @endif
                </div>
            </div>
            <!-- Progress Bar -->
            <div class="mt-3">
                <div class="progress-bar">
                    @php
                        $annualUsedPercent = $balances['annual']['entitlement'] > 0 
                            ? (($balances['annual']['used'] + $balances['annual']['pending']) / $balances['annual']['entitlement']) * 100 
                            : 0;
                    @endphp
                    <div class="progress-bar-fill" style="width: {{ min($annualUsedPercent, 100) }}%"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ round($annualUsedPercent) }}% used</p>
            </div>
        </div>

        <!-- MC Balance -->
        <div class="bg-white rounded-xl shadow-md p-5 card-hover">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span>
                    Medical Leave
                </h3>
                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-700 rounded">
                    {{ date('Y') }}
                </span>
            </div>
            <div class="flex items-end gap-4">
                <div class="flex-1">
                    <p class="text-3xl font-bold text-red-600">{{ $balances['mc']['available'] }}</p>
                    <p class="text-sm text-gray-500">days available</p>
                </div>
                <div class="text-right text-sm">
                    <p class="text-gray-600">Entitlement: <span class="font-medium">{{ $balances['mc']['entitlement'] }}</span></p>
                    <p class="text-green-600">Used: <span class="font-medium">{{ $balances['mc']['used'] }}</span></p>
                    @if($balances['mc']['pending'] > 0)
                    <p class="text-amber-600">Pending: <span class="font-medium">{{ $balances['mc']['pending'] }}</span></p>
                    @endif
                </div>
            </div>
            <!-- Progress Bar -->
            <div class="mt-3">
                <div class="progress-bar">
                    @php
                        $mcUsedPercent = $balances['mc']['entitlement'] > 0 
                            ? (($balances['mc']['used'] + $balances['mc']['pending']) / $balances['mc']['entitlement']) * 100 
                            : 0;
                    @endphp
                    <div class="progress-bar-fill" style="width: {{ min($mcUsedPercent, 100) }}%; background: linear-gradient(90deg, #ef4444, #f97316);"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ round($mcUsedPercent) }}% used</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-4 card-hover">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total This Year</p>
                    <p class="text-xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4 card-hover">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Days Approved</p>
                    <p class="text-xl font-bold text-green-600">{{ $stats['approved'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4 card-hover">
            <div class="flex items-center">
                <div class="p-3 bg-amber-100 rounded-lg">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Pending</p>
                    <p class="text-xl font-bold text-amber-600">{{ $stats['pending'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-md p-4 mb-6">
        <form method="GET" action="{{ route('leave.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Types</option>
                    <option value="annual" {{ request('type') === 'annual' ? 'selected' : '' }}>Annual Leave</option>
                    <option value="mc" {{ request('type') === 'mc' ? 'selected' : '' }}>Medical Leave</option>
                    <option value="emergency" {{ request('type') === 'emergency' ? 'selected' : '' }}>Emergency Leave</option>
                    <option value="unpaid" {{ request('type') === 'unpaid' ? 'selected' : '' }}>Unpaid Leave</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary px-4 py-2 rounded-lg ripple">
                    Filter
                </button>
                <a href="{{ route('leave.index') }}" class="btn-secondary px-4 py-2 rounded-lg">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Leave Requests Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        @if($leaves->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($leaves as $leave)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900">{{ $leave->getTypeLabel() }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $leave->start_date->format('d M Y') }}
                                        @if($leave->start_date->ne($leave->end_date))
                                            - {{ $leave->end_date->format('d M Y') }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">{{ $leave->days }} day{{ $leave->days > 1 ? 's' : '' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $leave->getStatusBadgeClass() }}">
                                        {{ ucfirst($leave->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $leave->created_at->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('leave.show', $leave) }}" 
                                           class="text-blue-600 hover:text-blue-900" title="View Details">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        @if($leave->status === 'pending')
                                            <form action="{{ route('leave.destroy', $leave) }}" 
                                                  method="POST"
                                                  onsubmit="return confirm('Are you sure you want to cancel this request?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" title="Cancel">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($leaves->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $leaves->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No leave requests</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by submitting a new leave request.</p>
                <div class="mt-6">
                    <a href="{{ route('leave.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        New Request
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
