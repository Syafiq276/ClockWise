@extends('layouts.app')

@section('title', 'Generate Payroll')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Generate Payroll</h1>
        <p class="text-gray-600">Calculate and generate payroll for an employee</p>
    </div>

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
            {{ session('error') }}
        </div>
    @endif

    <!-- Step 1: Select Employee and Period -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <span class="w-7 h-7 rounded-full bg-blue-600 text-white text-sm flex items-center justify-center mr-2">1</span>
            Select Employee & Period
        </h2>
        
        <form id="calculateForm" action="{{ route('admin.payroll.calculate') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Employee <span class="text-red-500">*</span></label>
                    <select name="employee_id" id="employee_id" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select Employee</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" 
                                    data-hourly-rate="{{ $employee->hourly_rate ?? 10 }}"
                                    {{ old('employee_id', $selectedEmployee ?? '') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }} ({{ $employee->position ?? 'Staff' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Month/Year <span class="text-red-500">*</span></label>
                    <input type="month" 
                           name="month_year" 
                           id="month_year" 
                           value="{{ old('month_year', $selectedMonth ?? date('Y-m')) }}"
                           max="{{ date('Y-m') }}"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    Calculate Payroll
                </button>
            </div>
        </form>
    </div>

    <!-- Step 2: Review & Adjust (shown after calculation) -->
    @if(isset($calculation))
        <form action="{{ route('admin.payroll.store') }}" method="POST">
            @csrf
            <input type="hidden" name="user_id" value="{{ $calculation['user_id'] }}">
            <input type="hidden" name="month_year" value="{{ $calculation['month_year'] }}">
            <input type="hidden" name="period_start" value="{{ $calculation['period_start'] }}">
            <input type="hidden" name="period_end" value="{{ $calculation['period_end'] }}">
            <input type="hidden" name="days_worked" value="{{ $calculation['days_worked'] }}">
            <input type="hidden" name="total_hours" value="{{ $calculation['total_hours'] }}">
            <input type="hidden" name="overtime_hours" value="{{ $calculation['overtime_hours'] }}">

            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="w-7 h-7 rounded-full bg-blue-600 text-white text-sm flex items-center justify-center mr-2">2</span>
                    Review Calculation
                </h2>
                
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div>
                            <p class="text-sm text-gray-500">Employee</p>
                            <p class="font-semibold text-gray-800">{{ $calculation['employee_name'] }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Period</p>
                            <p class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($calculation['month_year'] . '-01')->format('F Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Days Worked</p>
                            <p class="font-semibold text-gray-800">{{ $calculation['days_worked'] }} days</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Hours</p>
                            <p class="font-semibold text-gray-800">{{ $calculation['total_hours'] }} hours</p>
                        </div>
                    </div>
                </div>

                <!-- Hours Breakdown -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Hours Breakdown</h3>
                        <table class="w-full text-sm">
                            <tr class="border-b">
                                <td class="py-2 text-gray-600">Regular Hours</td>
                                <td class="py-2 text-right font-medium">{{ $calculation['regular_hours'] }} hours</td>
                            </tr>
                            <tr class="border-b">
                                <td class="py-2 text-gray-600">Overtime Hours</td>
                                <td class="py-2 text-right font-medium text-purple-600">{{ $calculation['overtime_hours'] }} hours</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-gray-600 font-medium">Total Hours</td>
                                <td class="py-2 text-right font-bold">{{ $calculation['total_hours'] }} hours</td>
                            </tr>
                        </table>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Rate Settings</h3>
                        <div class="mb-3">
                            <label class="block text-sm text-gray-600 mb-1">Hourly Rate (RM)</label>
                            <input type="number" 
                                   name="hourly_rate" 
                                   id="hourly_rate"
                                   value="{{ $calculation['hourly_rate'] }}"
                                   step="0.01"
                                   min="0"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   onchange="recalculate()">
                        </div>
                        <p class="text-xs text-gray-500">Overtime rate: 1.5x hourly rate</p>
                    </div>
                </div>
            </div>

            <!-- Step 3: Adjustments -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="w-7 h-7 rounded-full bg-blue-600 text-white text-sm flex items-center justify-center mr-2">3</span>
                    Adjustments
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Allowances -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-3 text-green-600">Allowances (+)</h3>
                        <div class="mb-3">
                            <label class="block text-sm text-gray-600 mb-1">Amount (RM)</label>
                            <input type="number" 
                                   name="allowances" 
                                   id="allowances"
                                   value="{{ old('allowances', 0) }}"
                                   step="0.01"
                                   min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   onchange="recalculate()">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Notes</label>
                            <input type="text" 
                                   name="allowance_notes" 
                                   placeholder="e.g., Transport, Meal"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Deductions -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-3 text-red-600">Deductions (-)</h3>
                        <div class="mb-3">
                            <label class="block text-sm text-gray-600 mb-1">Amount (RM)</label>
                            <input type="number" 
                                   name="deductions" 
                                   id="deductions"
                                   value="{{ old('deductions', 0) }}"
                                   step="0.01"
                                   min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   onchange="recalculate()">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Notes</label>
                            <input type="text" 
                                   name="deduction_notes" 
                                   placeholder="e.g., EPF, SOCSO, Late penalty"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 4: Summary -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="w-7 h-7 rounded-full bg-blue-600 text-white text-sm flex items-center justify-center mr-2">4</span>
                    Payment Summary
                </h2>

                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6">
                    <table class="w-full text-sm">
                        <tr class="border-b border-blue-200">
                            <td class="py-2 text-gray-600">Basic Pay ({{ $calculation['regular_hours'] }}h × RM <span id="display_rate">{{ $calculation['hourly_rate'] }}</span>)</td>
                            <td class="py-2 text-right font-medium">RM <span id="basic_pay">{{ number_format($calculation['basic_pay'], 2) }}</span></td>
                        </tr>
                        <tr class="border-b border-blue-200">
                            <td class="py-2 text-gray-600">Overtime Pay ({{ $calculation['overtime_hours'] }}h × 1.5)</td>
                            <td class="py-2 text-right font-medium text-purple-600">RM <span id="overtime_pay">{{ number_format($calculation['overtime_pay'], 2) }}</span></td>
                        </tr>
                        <tr class="border-b border-blue-200">
                            <td class="py-2 text-gray-600 font-medium">Gross Pay</td>
                            <td class="py-2 text-right font-bold">RM <span id="gross_pay">{{ number_format($calculation['gross_pay'], 2) }}</span></td>
                        </tr>
                        <tr class="border-b border-blue-200">
                            <td class="py-2 text-green-600">Allowances (+)</td>
                            <td class="py-2 text-right font-medium text-green-600">RM <span id="display_allowances">0.00</span></td>
                        </tr>
                        <tr class="border-b border-blue-200">
                            <td class="py-2 text-red-600">Deductions (-)</td>
                            <td class="py-2 text-right font-medium text-red-600">RM <span id="display_deductions">0.00</span></td>
                        </tr>
                        <tr>
                            <td class="py-3 text-lg font-bold text-gray-800">Net Pay</td>
                            <td class="py-3 text-right text-2xl font-bold text-blue-600">RM <span id="net_pay">{{ number_format($calculation['gross_pay'], 2) }}</span></td>
                        </tr>
                    </table>
                </div>

                <!-- Hidden fields for calculated values -->
                <input type="hidden" name="gross_pay" id="input_gross_pay" value="{{ $calculation['gross_pay'] }}">
                <input type="hidden" name="overtime_pay" id="input_overtime_pay" value="{{ $calculation['overtime_pay'] }}">
                <input type="hidden" name="net_pay" id="input_net_pay" value="{{ $calculation['gross_pay'] }}">
            </div>

            <!-- Actions -->
            <div class="flex justify-between items-center">
                <a href="{{ route('admin.payroll.index') }}" class="text-gray-600 hover:text-gray-800">
                    ← Back to Payroll List
                </a>
                <div class="flex gap-3">
                    <button type="submit" name="status" value="draft" 
                            class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Save as Draft
                    </button>
                    <button type="submit" name="status" value="approved" 
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        Save & Approve
                    </button>
                </div>
            </div>
        </form>

        <script>
            // Store initial values
            const regularHours = {{ $calculation['regular_hours'] }};
            const overtimeHours = {{ $calculation['overtime_hours'] }};

            function recalculate() {
                const hourlyRate = parseFloat(document.getElementById('hourly_rate').value) || 0;
                const allowances = parseFloat(document.getElementById('allowances').value) || 0;
                const deductions = parseFloat(document.getElementById('deductions').value) || 0;

                // Calculate pays
                const basicPay = regularHours * hourlyRate;
                const overtimePay = overtimeHours * hourlyRate * 1.5;
                const grossPay = basicPay + overtimePay;
                const netPay = grossPay + allowances - deductions;

                // Update display
                document.getElementById('display_rate').textContent = hourlyRate.toFixed(2);
                document.getElementById('basic_pay').textContent = basicPay.toFixed(2);
                document.getElementById('overtime_pay').textContent = overtimePay.toFixed(2);
                document.getElementById('gross_pay').textContent = grossPay.toFixed(2);
                document.getElementById('display_allowances').textContent = allowances.toFixed(2);
                document.getElementById('display_deductions').textContent = deductions.toFixed(2);
                document.getElementById('net_pay').textContent = netPay.toFixed(2);

                // Update hidden inputs
                document.getElementById('input_gross_pay').value = grossPay.toFixed(2);
                document.getElementById('input_overtime_pay').value = overtimePay.toFixed(2);
                document.getElementById('input_net_pay').value = netPay.toFixed(2);
            }
        </script>
    @endif
</div>
@endsection
