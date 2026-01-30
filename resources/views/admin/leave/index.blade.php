@extends('layouts.app')

@section('title', 'Leave Requests Management')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Leave Requests</h1>
        <p class="text-gray-600">Review and manage employee leave applications</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-4">
            <div class="flex items-center">
                <div class="p-3 bg-amber-100 rounded-lg">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Pending Review</p>
                    <p class="text-xl font-bold text-amber-600">{{ $stats['pending'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Approved This Month</p>
                    <p class="text-xl font-bold text-green-600">{{ $stats['approved_this_month'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total This Month</p>
                    <p class="text-xl font-bold text-blue-600">{{ $stats['total_this_month'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-md p-4 mb-6">
        <form method="GET" action="{{ route('admin.leave') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                <select name="employee" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Employees</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ request('employee') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[130px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="flex-1 min-w-[130px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Types</option>
                    <option value="annual" {{ request('type') === 'annual' ? 'selected' : '' }}>Annual Leave</option>
                    <option value="mc" {{ request('type') === 'mc' ? 'selected' : '' }}>Medical Leave</option>
                    <option value="emergency" {{ request('type') === 'emergency' ? 'selected' : '' }}>Emergency</option>
                    <option value="unpaid" {{ request('type') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Filter
                </button>
                <a href="{{ route('admin.leave') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Leave Requests -->
    <div class="space-y-4">
        @forelse($leaves as $leave)
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-6">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                        <!-- Employee Info & Leave Details -->
                        <div class="flex-1">
                            <div class="flex items-center mb-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 font-semibold text-sm">
                                        {{ strtoupper(substr($leave->user->name ?? 'U', 0, 2)) }}
                                    </span>
                                </div>
                                <div class="ml-3">
                                    <p class="font-medium text-gray-900">{{ $leave->user->name ?? 'Unknown' }}</p>
                                    <p class="text-sm text-gray-500">{{ $leave->user->position ?? 'Employee' }}</p>
                                </div>
                                <span class="ml-auto inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $leave->getStatusBadgeClass() }}">
                                    {{ ucfirst($leave->status) }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                                <div>
                                    <p class="text-gray-500">Type</p>
                                    <p class="font-medium">{{ $leave->getTypeLabel() }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Dates</p>
                                    <p class="font-medium">
                                        {{ $leave->start_date->format('d M') }}
                                        @if($leave->start_date->ne($leave->end_date))
                                            - {{ $leave->end_date->format('d M') }}
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Duration</p>
                                    <p class="font-medium">{{ $leave->days }} day{{ $leave->days > 1 ? 's' : '' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Submitted</p>
                                    <p class="font-medium">{{ $leave->created_at->format('d M Y') }}</p>
                                </div>
                            </div>

                            <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-600"><span class="font-medium">Reason:</span> {{ Str::limit($leave->reason, 150) }}</p>
                            </div>

                            @if($leave->attachment)
                                <a href="{{ Storage::url($leave->attachment) }}" 
                                   target="_blank"
                                   class="inline-flex items-center mt-3 text-sm text-blue-600 hover:text-blue-800">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                    View Attachment
                                </a>
                            @endif
                        </div>

                        <!-- Actions -->
                        @if($leave->status === 'pending')
                            <div class="flex flex-col gap-2 lg:w-48">
                                <!-- Approve Button -->
                                <form action="{{ route('admin.leave.approve', $leave) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition inline-flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Approve
                                    </button>
                                </form>

                                <!-- Reject Button (opens modal) -->
                                <button type="button" 
                                        onclick="openRejectModal({{ $leave->id }})"
                                        class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition inline-flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Reject
                                </button>
                            </div>
                        @else
                            <div class="lg:w-48 text-sm text-gray-500">
                                @if($leave->admin_remarks)
                                    <p class="font-medium text-gray-700">Remarks:</p>
                                    <p>{{ $leave->admin_remarks }}</p>
                                @endif
                                @if($leave->approver)
                                    <p class="mt-2">By: {{ $leave->approver->name }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow-md p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No leave requests</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if(request()->hasAny(['employee', 'status', 'type']))
                        No requests match your filters.
                    @else
                        Leave requests will appear here when employees submit them.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($leaves->hasPages())
        <div class="mt-6">
            {{ $leaves->links() }}
        </div>
    @endif

    <!-- Back Link -->
    <div class="mt-6">
        <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800 inline-flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Dashboard
        </a>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" onclick="closeRejectModal()"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Reject Leave Request</h3>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1">
                        Reason for rejection <span class="text-red-500">*</span>
                    </label>
                    <textarea name="remarks" 
                              id="remarks" 
                              rows="3" 
                              required
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Please provide a reason..."></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" 
                            onclick="closeRejectModal()"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        Reject Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openRejectModal(leaveId) {
    document.getElementById('rejectForm').action = `/admin/leave/${leaveId}/reject`;
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('remarks').value = '';
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeRejectModal();
    }
});
</script>
@endsection
