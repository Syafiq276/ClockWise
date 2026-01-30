@extends('layouts.app')

@section('title', 'Leave Request Details')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('leave.index') }}" class="text-blue-600 hover:text-blue-800 inline-flex items-center mb-2">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to My Requests
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Leave Request Details</h1>
    </div>

    <!-- Status Banner -->
    <div class="mb-6 p-4 rounded-xl {{ $leave->status === 'approved' ? 'bg-green-50 border border-green-200' : ($leave->status === 'rejected' ? 'bg-red-50 border border-red-200' : 'bg-amber-50 border border-amber-200') }}">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                @if($leave->status === 'approved')
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="ml-2 font-medium text-green-800">Approved</span>
                @elseif($leave->status === 'rejected')
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="ml-2 font-medium text-red-800">Rejected</span>
                @else
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="ml-2 font-medium text-amber-800">Pending Approval</span>
                @endif
            </div>
            @if($leave->responded_at)
                <span class="text-sm {{ $leave->status === 'approved' ? 'text-green-600' : 'text-red-600' }}">
                    {{ $leave->responded_at->format('d M Y, h:i A') }}
                </span>
            @endif
        </div>
        @if($leave->admin_remarks)
            <div class="mt-3 pt-3 border-t {{ $leave->status === 'approved' ? 'border-green-200' : 'border-red-200' }}">
                <p class="text-sm {{ $leave->status === 'approved' ? 'text-green-700' : 'text-red-700' }}">
                    <span class="font-medium">Admin remarks:</span> {{ $leave->admin_remarks }}
                </p>
            </div>
        @endif
    </div>

    <!-- Details Card -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="p-6 space-y-4">
            <!-- Leave Type -->
            <div class="flex justify-between py-3 border-b border-gray-100">
                <span class="text-gray-500">Leave Type</span>
                <span class="font-medium text-gray-900">{{ $leave->getTypeLabel() }}</span>
            </div>

            <!-- Date Range -->
            <div class="flex justify-between py-3 border-b border-gray-100">
                <span class="text-gray-500">Dates</span>
                <span class="font-medium text-gray-900">
                    {{ $leave->start_date->format('d M Y') }}
                    @if($leave->start_date->ne($leave->end_date))
                        - {{ $leave->end_date->format('d M Y') }}
                    @endif
                </span>
            </div>

            <!-- Duration -->
            <div class="flex justify-between py-3 border-b border-gray-100">
                <span class="text-gray-500">Duration</span>
                <span class="font-medium text-gray-900">{{ $leave->days }} day{{ $leave->days > 1 ? 's' : '' }}</span>
            </div>

            <!-- Submitted -->
            <div class="flex justify-between py-3 border-b border-gray-100">
                <span class="text-gray-500">Submitted</span>
                <span class="font-medium text-gray-900">{{ $leave->created_at->format('d M Y, h:i A') }}</span>
            </div>

            <!-- Reason -->
            <div class="py-3">
                <span class="text-gray-500 block mb-2">Reason</span>
                <p class="text-gray-900 bg-gray-50 rounded-lg p-3">{{ $leave->reason }}</p>
            </div>

            <!-- Attachment -->
            @if($leave->attachment)
                <div class="py-3 border-t border-gray-100">
                    <span class="text-gray-500 block mb-2">Attachment</span>
                    <a href="{{ Storage::url($leave->attachment) }}" 
                       target="_blank"
                       class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                        View Attachment
                    </a>
                </div>
            @endif

            <!-- Approved By -->
            @if($leave->approver)
                <div class="py-3 border-t border-gray-100">
                    <span class="text-gray-500 block mb-1">Processed By</span>
                    <span class="font-medium text-gray-900">{{ $leave->approver->name }}</span>
                </div>
            @endif
        </div>

        <!-- Actions -->
        @if($leave->status === 'pending')
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <form action="{{ route('leave.destroy', $leave) }}" 
                      method="POST"
                      onsubmit="return confirm('Are you sure you want to cancel this request?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="w-full sm:w-auto px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        Cancel Request
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
