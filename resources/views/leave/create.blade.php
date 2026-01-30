@extends('layouts.app')

@section('title', 'New Leave Request')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">New Leave Request</h1>
        <p class="text-gray-600">Submit a request for time off</p>
    </div>

    <!-- Leave Balance Cards -->
    @if(isset($balances))
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2 h-2 rounded-full bg-blue-500 pulse-dot green"></span>
                <span class="text-sm font-medium text-blue-800">Annual Leave</span>
            </div>
            <p class="text-2xl font-bold text-blue-600">{{ $balances['annual']['available'] }}</p>
            <p class="text-xs text-blue-600/70">of {{ $balances['annual']['entitlement'] }} days available</p>
        </div>
        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-4 border border-red-200">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2 h-2 rounded-full bg-red-500 pulse-dot green"></span>
                <span class="text-sm font-medium text-red-800">Medical Leave</span>
            </div>
            <p class="text-2xl font-bold text-red-600">{{ $balances['mc']['available'] }}</p>
            <p class="text-xs text-red-600/70">of {{ $balances['mc']['entitlement'] }} days available</p>
        </div>
    </div>
    @endif

    <!-- Form Card -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <form method="POST" action="{{ route('leave.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Leave Type -->
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                    Leave Type <span class="text-red-500">*</span>
                </label>
                <select name="type" 
                        id="type" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('type') border-red-500 @enderror">
                    <option value="">Select type...</option>
                    <option value="annual" {{ old('type') === 'annual' ? 'selected' : '' }} data-balance="{{ $balances['annual']['available'] ?? 0 }}">Annual Leave ({{ $balances['annual']['available'] ?? 0 }} days left)</option>
                    <option value="mc" {{ old('type') === 'mc' ? 'selected' : '' }} data-balance="{{ $balances['mc']['available'] ?? 0 }}">Medical Leave ({{ $balances['mc']['available'] ?? 0 }} days left)</option>
                    <option value="emergency" {{ old('type') === 'emergency' ? 'selected' : '' }}>Emergency Leave</option>
                    <option value="unpaid" {{ old('type') === 'unpaid' ? 'selected' : '' }}>Unpaid Leave</option>
                </select>
                @error('type')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Balance Warning -->
            <div id="balanceWarning" class="hidden p-3 bg-amber-50 border border-amber-200 rounded-lg">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-amber-800">Insufficient Leave Balance</p>
                        <p class="text-xs text-amber-700" id="balanceWarningText"></p>
                    </div>
                </div>
            </div>

            <!-- Date Range -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
                        Start Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           name="start_date" 
                           id="start_date" 
                           value="{{ old('start_date') }}"
                           min="{{ date('Y-m-d') }}"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('start_date') border-red-500 @enderror">
                    @error('start_date')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">
                        End Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           name="end_date" 
                           id="end_date" 
                           value="{{ old('end_date') }}"
                           min="{{ date('Y-m-d') }}"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('end_date') border-red-500 @enderror">
                    @error('end_date')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Days Preview -->
            <div id="daysPreview" class="hidden p-3 bg-blue-50 rounded-lg">
                <p class="text-sm text-blue-800">
                    <span class="font-medium">Duration:</span> <span id="daysCount">1</span> day(s)
                </p>
            </div>

            <!-- Reason -->
            <div>
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">
                    Reason <span class="text-red-500">*</span>
                </label>
                <textarea name="reason" 
                          id="reason" 
                          rows="4"
                          required
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('reason') border-red-500 @enderror"
                          placeholder="Please provide details about your leave request...">{{ old('reason') }}</textarea>
                @error('reason')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Attachment (for MC) -->
            <div id="attachmentSection">
                <label for="attachment" class="block text-sm font-medium text-gray-700 mb-1">
                    Attachment <span class="text-gray-400">(Optional - MC certificate, etc.)</span>
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="attachment" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                <span>Upload a file</span>
                                <input id="attachment" name="attachment" type="file" class="sr-only" accept=".pdf,.jpg,.jpeg,.png">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">PDF, JPG, PNG up to 2MB</p>
                    </div>
                </div>
                <p id="fileName" class="mt-2 text-sm text-gray-600 hidden"></p>
                @error('attachment')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="{{ route('leave.index') }}" 
                   class="btn-secondary px-4 py-2 rounded-lg">
                    Cancel
                </a>
                <button type="submit" 
                        id="submitBtn"
                        class="btn-primary px-6 py-2 rounded-lg ripple relative">
                    <span class="btn-text">Submit Request</span>
                    <span class="btn-loader"></span>
                </button>
            </div>
        </form>
    </div>

    <!-- Info Box -->
    <div class="mt-6 p-4 bg-amber-50 rounded-xl border border-amber-200">
        <div class="flex">
            <svg class="w-5 h-5 text-amber-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="ml-3">
                <h4 class="text-sm font-medium text-amber-800">Note</h4>
                <p class="text-sm text-amber-700 mt-1">
                    Please submit your leave request at least 3 days in advance for annual leave. 
                    Medical leave requires a valid MC certificate attachment.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Elements
const startDate = document.getElementById('start_date');
const endDate = document.getElementById('end_date');
const daysPreview = document.getElementById('daysPreview');
const daysCount = document.getElementById('daysCount');
const typeSelect = document.getElementById('type');
const balanceWarning = document.getElementById('balanceWarning');
const balanceWarningText = document.getElementById('balanceWarningText');

// Leave balances from server
const balances = {
    annual: {{ $balances['annual']['available'] ?? 0 }},
    mc: {{ $balances['mc']['available'] ?? 0 }}
};

function calculateDays() {
    if (startDate.value && endDate.value) {
        const start = new Date(startDate.value);
        const end = new Date(endDate.value);
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        
        if (diffDays > 0) {
            daysCount.textContent = diffDays;
            daysPreview.classList.remove('hidden');
            checkBalance(diffDays);
            return diffDays;
        }
    }
    return 0;
}

function checkBalance(days) {
    const selectedType = typeSelect.value;
    
    if (selectedType === 'annual' && days > balances.annual) {
        balanceWarning.classList.remove('hidden');
        balanceWarningText.textContent = `You're requesting ${days} days but only have ${balances.annual} days of annual leave available. The request may be partially converted to unpaid leave.`;
    } else if (selectedType === 'mc' && days > balances.mc) {
        balanceWarning.classList.remove('hidden');
        balanceWarningText.textContent = `You're requesting ${days} days but only have ${balances.mc} days of medical leave available.`;
    } else {
        balanceWarning.classList.add('hidden');
    }
}

typeSelect.addEventListener('change', function() {
    const days = calculateDays();
    if (days > 0) {
        checkBalance(days);
    }
});

startDate.addEventListener('change', function() {
    endDate.min = this.value;
    if (endDate.value && endDate.value < this.value) {
        endDate.value = this.value;
    }
    calculateDays();
});

endDate.addEventListener('change', calculateDays);

// File name display
document.getElementById('attachment').addEventListener('change', function(e) {
    const fileName = document.getElementById('fileName');
    if (e.target.files.length > 0) {
        fileName.textContent = 'Selected: ' + e.target.files[0].name;
        fileName.classList.remove('hidden');
    } else {
        fileName.classList.add('hidden');
    }
});
</script>
@endsection
