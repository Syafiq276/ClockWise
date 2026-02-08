@extends('layouts.app')

@section('title', 'Generate Payroll')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Generate Payroll</h1>
        <p class="text-gray-600">Calculate and generate payroll with Malaysian statutory deductions</p>
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
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
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

                <!-- Hours & Rate -->
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

            <!-- Step 3: Statutory Deductions -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="w-7 h-7 rounded-full bg-blue-600 text-white text-sm flex items-center justify-center mr-2">3</span>
                    Statutory Deductions (Malaysian)
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- EPF/KWSP -->
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-xs font-bold mr-2">EPF</div>
                            <div>
                                <h4 class="font-medium text-gray-800">KWSP</h4>
                                <p class="text-xs text-gray-500">Employees Provident Fund</p>
                            </div>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Employee ({{ $calculation['epf_rate_employee'] }}%)</span>
                                <span class="font-medium text-red-600">- RM <span id="epf_employee_display">{{ number_format($calculation['epf_employee'], 2) }}</span></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Employer ({{ $calculation['epf_rate_employer'] }}%)</span>
                                <span class="font-medium text-blue-600">RM <span id="epf_employer_display">{{ number_format($calculation['epf_employer'], 2) }}</span></span>
                            </div>
                        </div>
                        <input type="hidden" name="epf_employee" id="epf_employee" value="{{ $calculation['epf_employee'] }}">
                        <input type="hidden" name="epf_employer" id="epf_employer" value="{{ $calculation['epf_employer'] }}">
                        <input type="hidden" name="epf_rate_employee" value="{{ $calculation['epf_rate_employee'] }}">
                        <input type="hidden" name="epf_rate_employer" value="{{ $calculation['epf_rate_employer'] }}">
                    </div>

                    <!-- SOCSO/PERKESO -->
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center text-white text-xs font-bold mr-2">SOC</div>
                            <div>
                                <h4 class="font-medium text-gray-800">PERKESO</h4>
                                <p class="text-xs text-gray-500">Social Security Organization</p>
                            </div>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Employee</span>
                                <span class="font-medium text-red-600">- RM <span id="socso_employee_display">{{ number_format($calculation['socso_employee'], 2) }}</span></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Employer</span>
                                <span class="font-medium text-blue-600">RM <span id="socso_employer_display">{{ number_format($calculation['socso_employer'], 2) }}</span></span>
                            </div>
                        </div>
                        <input type="hidden" name="socso_employee" id="socso_employee" value="{{ $calculation['socso_employee'] }}">
                        <input type="hidden" name="socso_employer" id="socso_employer" value="{{ $calculation['socso_employer'] }}">
                    </div>

                    <!-- EIS/SIP -->
                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <div class="w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center text-white text-xs font-bold mr-2">EIS</div>
                            <div>
                                <h4 class="font-medium text-gray-800">SIP</h4>
                                <p class="text-xs text-gray-500">Employment Insurance System</p>
                            </div>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Employee (0.2%)</span>
                                <span class="font-medium text-red-600">- RM <span id="eis_employee_display">{{ number_format($calculation['eis_employee'], 2) }}</span></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Employer (0.2%)</span>
                                <span class="font-medium text-blue-600">RM <span id="eis_employer_display">{{ number_format($calculation['eis_employer'], 2) }}</span></span>
                            </div>
                        </div>
                        <input type="hidden" name="eis_employee" id="eis_employee" value="{{ $calculation['eis_employee'] }}">
                        <input type="hidden" name="eis_employer" id="eis_employer" value="{{ $calculation['eis_employer'] }}">
                    </div>
                </div>

                <!-- PCB/MTD -->
                <div class="mt-4 p-4 bg-amber-50 rounded-lg">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-amber-600 rounded-full flex items-center justify-center text-white text-xs font-bold mr-2">PCB</div>
                            <div>
                                <h4 class="font-medium text-gray-800">PCB / MTD</h4>
                                <p class="text-xs text-gray-500">Monthly Tax Deduction (Manual Entry)</p>
                            </div>
                        </div>
                        <div class="w-32">
                            <input type="number" 
                                   name="pcb" 
                                   id="pcb"
                                   value="0"
                                   step="0.01"
                                   min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-right"
                                   onchange="recalculate()">
                        </div>
                    </div>
                </div>

                <!-- Statutory Summary -->
                <div class="mt-4 p-4 bg-gray-100 rounded-lg">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-600">Total Employee Statutory Deductions</p>
                            <p class="text-xl font-bold text-red-600">- RM <span id="total_statutory_display">{{ number_format($calculation['total_statutory'], 2) }}</span></p>
                        </div>
                        <div class="text-right">
                            <p class="text-gray-600">Total Employer Contributions</p>
                            <p class="text-xl font-bold text-blue-600">RM <span id="employer_contribution_display">{{ number_format($calculation['employer_contribution'], 2) }}</span></p>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="total_statutory" id="total_statutory" value="{{ $calculation['total_statutory'] }}">
                <input type="hidden" name="employer_contribution" id="employer_contribution" value="{{ $calculation['employer_contribution'] }}">
            </div>

            <!-- Step 4: Other Adjustments -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="w-7 h-7 rounded-full bg-blue-600 text-white text-sm flex items-center justify-center mr-2">4</span>
                    Other Adjustments
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
                                   placeholder="e.g., Transport, Meal, Phone"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Other Deductions -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-3 text-red-600">Other Deductions (-)</h3>
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
                                   placeholder="e.g., Loan, Advance, Late penalty"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 5: Payment Summary -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="w-7 h-7 rounded-full bg-blue-600 text-white text-sm flex items-center justify-center mr-2">5</span>
                    Payment Summary
                </h2>

                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6">
                    <table class="w-full text-sm">
                        <tbody>
                            <tr class="border-b border-blue-200">
                                <td class="py-2 text-gray-600">Basic Pay ({{ $calculation['regular_hours'] }}h × RM <span id="display_rate">{{ $calculation['hourly_rate'] }}</span>)</td>
                                <td class="py-2 text-right font-medium">RM <span id="basic_pay">{{ number_format($calculation['basic_pay'], 2) }}</span></td>
                            </tr>
                            <tr class="border-b border-blue-200">
                                <td class="py-2 text-gray-600">Overtime Pay ({{ $calculation['overtime_hours'] }}h × 1.5)</td>
                                <td class="py-2 text-right font-medium text-purple-600">RM <span id="overtime_pay">{{ number_format($calculation['overtime_pay'], 2) }}</span></td>
                            </tr>
                            <tr class="border-b border-blue-200 bg-blue-100">
                                <td class="py-2 text-gray-700 font-medium">Gross Pay</td>
                                <td class="py-2 text-right font-bold">RM <span id="gross_pay">{{ number_format($calculation['gross_pay'], 2) }}</span></td>
                            </tr>
                            <tr class="border-b border-blue-200">
                                <td class="py-2 text-green-600">Allowances (+)</td>
                                <td class="py-2 text-right font-medium text-green-600">RM <span id="display_allowances">0.00</span></td>
                            </tr>
                        </tbody>
                        <tbody class="bg-red-50">
                            <tr class="border-b border-red-200">
                                <td class="py-2 text-red-600 pl-4">EPF (KWSP)</td>
                                <td class="py-2 text-right font-medium text-red-600">- RM <span id="summary_epf">{{ number_format($calculation['epf_employee'], 2) }}</span></td>
                            </tr>
                            <tr class="border-b border-red-200">
                                <td class="py-2 text-red-600 pl-4">SOCSO (PERKESO)</td>
                                <td class="py-2 text-right font-medium text-red-600">- RM <span id="summary_socso">{{ number_format($calculation['socso_employee'], 2) }}</span></td>
                            </tr>
                            <tr class="border-b border-red-200">
                                <td class="py-2 text-red-600 pl-4">EIS (SIP)</td>
                                <td class="py-2 text-right font-medium text-red-600">- RM <span id="summary_eis">{{ number_format($calculation['eis_employee'], 2) }}</span></td>
                            </tr>
                            <tr class="border-b border-red-200">
                                <td class="py-2 text-red-600 pl-4">PCB (Tax)</td>
                                <td class="py-2 text-right font-medium text-red-600">- RM <span id="summary_pcb">0.00</span></td>
                            </tr>
                            <tr class="border-b border-red-200">
                                <td class="py-2 text-red-600 pl-4">Other Deductions</td>
                                <td class="py-2 text-right font-medium text-red-600">- RM <span id="display_deductions">0.00</span></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="bg-green-100">
                                <td class="py-3 text-lg font-bold text-gray-800">Net Pay (Take Home)</td>
                                <td class="py-3 text-right text-2xl font-bold text-green-600">RM <span id="net_pay">{{ number_format($calculation['gross_pay'] - $calculation['total_statutory'], 2) }}</span></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Hidden fields for calculated values -->
                <input type="hidden" name="gross_pay" id="input_gross_pay" value="{{ $calculation['gross_pay'] }}">
                <input type="hidden" name="overtime_pay" id="input_overtime_pay" value="{{ $calculation['overtime_pay'] }}">
                <input type="hidden" name="net_pay" id="input_net_pay" value="{{ $calculation['gross_pay'] - $calculation['total_statutory'] }}">
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
            const initEpfEmployee = {{ $calculation['epf_employee'] }};
            const initSocsoEmployee = {{ $calculation['socso_employee'] }};
            const initEisEmployee = {{ $calculation['eis_employee'] }};

            function recalculate() {
                const hourlyRate = parseFloat(document.getElementById('hourly_rate').value) || 0;
                const allowances = parseFloat(document.getElementById('allowances').value) || 0;
                const deductions = parseFloat(document.getElementById('deductions').value) || 0;
                const pcb = parseFloat(document.getElementById('pcb').value) || 0;

                // Calculate pays
                const basicPay = regularHours * hourlyRate;
                const overtimePay = overtimeHours * hourlyRate * 1.5;
                const grossPay = basicPay + overtimePay;

                // Recalculate EPF based on new gross
                const epfRateEmployee = {{ $calculation['epf_rate_employee'] }};
                const epfRateEmployer = {{ $calculation['epf_rate_employer'] }};
                const epfEmployee = Math.round(grossPay * (epfRateEmployee / 100) * 100) / 100;
                const epfEmployer = Math.round(grossPay * (epfRateEmployer / 100) * 100) / 100;

                // For SOCSO and EIS, keep same values (table-based)
                const socsoEmployee = initSocsoEmployee;
                const socsoEmployer = {{ $calculation['socso_employer'] }};
                const eisEmployee = initEisEmployee;
                const eisEmployer = {{ $calculation['eis_employer'] }};

                // Total statutory (employee portion + PCB)
                const totalStatutory = epfEmployee + socsoEmployee + eisEmployee + pcb;
                const employerContribution = epfEmployer + socsoEmployer + eisEmployer;

                // Net pay
                const netPay = grossPay + allowances - totalStatutory - deductions;

                // Update displays
                document.getElementById('display_rate').textContent = hourlyRate.toFixed(2);
                document.getElementById('basic_pay').textContent = basicPay.toFixed(2);
                document.getElementById('overtime_pay').textContent = overtimePay.toFixed(2);
                document.getElementById('gross_pay').textContent = grossPay.toFixed(2);
                document.getElementById('display_allowances').textContent = allowances.toFixed(2);
                document.getElementById('display_deductions').textContent = deductions.toFixed(2);

                // EPF
                document.getElementById('epf_employee_display').textContent = epfEmployee.toFixed(2);
                document.getElementById('epf_employer_display').textContent = epfEmployer.toFixed(2);
                document.getElementById('epf_employee').value = epfEmployee.toFixed(2);
                document.getElementById('epf_employer').value = epfEmployer.toFixed(2);

                // Summary
                document.getElementById('summary_epf').textContent = epfEmployee.toFixed(2);
                document.getElementById('summary_socso').textContent = socsoEmployee.toFixed(2);
                document.getElementById('summary_eis').textContent = eisEmployee.toFixed(2);
                document.getElementById('summary_pcb').textContent = pcb.toFixed(2);

                // Totals
                document.getElementById('total_statutory_display').textContent = totalStatutory.toFixed(2);
                document.getElementById('employer_contribution_display').textContent = employerContribution.toFixed(2);
                document.getElementById('total_statutory').value = totalStatutory.toFixed(2);
                document.getElementById('employer_contribution').value = employerContribution.toFixed(2);

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
