@extends('layouts.app')

@section('title', 'Payroll Details')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Payroll Details</h1>
            <p class="text-gray-600">{{ $payroll->user->name ?? 'Unknown' }} - {{ \Carbon\Carbon::parse($payroll->month_year . '-01')->format('F Y') }}</p>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $payroll->getStatusBadgeClass() }}">
            {{ ucfirst($payroll->status) }}
        </span>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif

    <!-- Payslip Card -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-6">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2">
                <div>
                    <h2 class="text-xl font-bold">ClockWise</h2>
                    <p class="text-blue-100 text-sm">Payslip</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-blue-100">Payroll ID</p>
                    <p class="font-mono font-bold">#{{ str_pad($payroll->id, 6, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>
        </div>

        <!-- Employee Info -->
        <div class="p-4 sm:p-6 border-b">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Employee Information</h3>
                    <p class="font-semibold text-gray-800">{{ $payroll->user->name ?? 'Unknown' }}</p>
                    <p class="text-sm text-gray-600">{{ $payroll->user->email ?? '-' }}</p>
                    <p class="text-sm text-gray-600">{{ $payroll->user->position ?? 'Staff' }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Pay Period</h3>
                    <p class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($payroll->month_year . '-01')->format('F Y') }}</p>
                    @if($payroll->period_start && $payroll->period_end)
                        <p class="text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($payroll->period_start)->format('d M') }} - 
                            {{ \Carbon\Carbon::parse($payroll->period_end)->format('d M Y') }}
                        </p>
                    @endif
                    <p class="text-sm text-gray-600">{{ $payroll->days_worked }} days worked</p>
                </div>
            </div>
        </div>

        <!-- Work Summary -->
        <div class="p-6 border-b">
            <h3 class="text-sm font-medium text-gray-500 mb-4">Work Summary</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                <div class="bg-blue-50 rounded-lg p-3">
                    <p class="text-xl font-bold text-blue-600">{{ $payroll->days_worked }}</p>
                    <p class="text-xs text-gray-500">Days</p>
                </div>
                <div class="bg-indigo-50 rounded-lg p-3">
                    <p class="text-xl font-bold text-indigo-600">{{ $payroll->total_hours }}</p>
                    <p class="text-xs text-gray-500">Total Hours</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-3">
                    <p class="text-xl font-bold text-purple-600">{{ $payroll->overtime_hours }}</p>
                    <p class="text-xs text-gray-500">OT Hours</p>
                </div>
                <div class="bg-green-50 rounded-lg p-3">
                    <p class="text-xl font-bold text-green-600">RM {{ number_format($payroll->hourly_rate, 2) }}</p>
                    <p class="text-xs text-gray-500">Hourly Rate</p>
                </div>
            </div>
        </div>

        <!-- Earnings -->
        <div class="p-6 border-b">
            <h3 class="text-sm font-medium text-gray-500 mb-4">Earnings</h3>
            <table class="w-full">
                <tbody class="divide-y divide-gray-100">
                    <tr>
                        <td class="py-2 text-gray-600">Basic Pay</td>
                        <td class="py-2 text-center text-gray-600">{{ $payroll->total_hours - $payroll->overtime_hours }} hrs × RM {{ number_format($payroll->hourly_rate, 2) }}</td>
                        <td class="py-2 text-right font-medium">RM {{ number_format(($payroll->total_hours - $payroll->overtime_hours) * $payroll->hourly_rate, 2) }}</td>
                    </tr>
                    @if($payroll->overtime_hours > 0)
                        <tr>
                            <td class="py-2 text-gray-600">Overtime</td>
                            <td class="py-2 text-center text-gray-600">{{ $payroll->overtime_hours }} hrs × RM {{ number_format($payroll->hourly_rate * 1.5, 2) }}</td>
                            <td class="py-2 text-right font-medium text-purple-600">RM {{ number_format($payroll->overtime_pay, 2) }}</td>
                        </tr>
                    @endif
                    @if($payroll->allowances > 0)
                        <tr>
                            <td class="py-2 text-gray-600">Allowances</td>
                            <td class="py-2 text-center text-gray-500 text-sm">{{ $payroll->allowance_notes ?? '-' }}</td>
                            <td class="py-2 text-right font-medium text-green-600">RM {{ number_format($payroll->allowances, 2) }}</td>
                        </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr class="border-t-2">
                        <td class="py-3 font-medium text-gray-800" colspan="2">Gross Pay</td>
                        <td class="py-3 text-right font-bold text-lg">RM {{ number_format($payroll->gross_pay + ($payroll->allowances ?? 0), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Statutory Deductions -->
        <div class="p-6 border-b bg-red-50">
            <h3 class="text-sm font-medium text-red-600 mb-4">Statutory Deductions (Employee)</h3>
            <table class="w-full">
                <tbody class="divide-y divide-red-100">
                    <tr>
                        <td class="py-2 text-gray-600">KWSP / EPF ({{ $payroll->epf_rate_employee ?? 11 }}%)</td>
                        <td class="py-2 text-right font-medium text-red-600">- RM {{ number_format($payroll->epf_employee ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">PERKESO / SOCSO</td>
                        <td class="py-2 text-right font-medium text-red-600">- RM {{ number_format($payroll->socso_employee ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">SIP / EIS</td>
                        <td class="py-2 text-right font-medium text-red-600">- RM {{ number_format($payroll->eis_employee ?? 0, 2) }}</td>
                    </tr>
                    @if(($payroll->pcb ?? 0) > 0)
                    <tr>
                        <td class="py-2 text-gray-600">PCB / MTD (Tax)</td>
                        <td class="py-2 text-right font-medium text-red-600">- RM {{ number_format($payroll->pcb, 2) }}</td>
                    </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-red-200">
                        <td class="py-2 font-medium text-gray-800">Total Statutory</td>
                        <td class="py-2 text-right font-bold text-red-600">- RM {{ number_format($payroll->total_statutory ?? 0, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Other Deductions -->
        @if($payroll->deductions > 0)
            <div class="p-6 border-b bg-orange-50">
                <h3 class="text-sm font-medium text-orange-600 mb-4">Other Deductions</h3>
                <table class="w-full">
                    <tbody>
                        <tr>
                            <td class="py-2 text-gray-600">{{ $payroll->deduction_notes ?? 'Other Deductions' }}</td>
                            <td class="py-2 text-right font-medium text-orange-600">- RM {{ number_format($payroll->deductions, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Net Pay -->
        <div class="p-6 bg-gradient-to-r from-green-50 to-emerald-50">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500">Net Pay (Take Home)</p>
                    <p class="text-3xl font-bold text-green-600">RM {{ number_format($payroll->net_pay, 2) }}</p>
                </div>
                <div class="text-right">
                    @if($payroll->paid_at)
                        <p class="text-sm text-gray-500">Paid on</p>
                        <p class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($payroll->paid_at)->format('d M Y') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Employer Contribution -->
        <div class="p-4 bg-blue-50 border-t">
            <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Employer Contribution</h4>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="text-gray-600">EPF:</span>
                    <span class="font-medium ml-1">RM {{ number_format($payroll->epf_employer ?? 0, 2) }}</span>
                </div>
                <div>
                    <span class="text-gray-600">SOCSO:</span>
                    <span class="font-medium ml-1">RM {{ number_format($payroll->socso_employer ?? 0, 2) }}</span>
                </div>
                <div>
                    <span class="text-gray-600">EIS:</span>
                    <span class="font-medium ml-1">RM {{ number_format($payroll->eis_employer ?? 0, 2) }}</span>
                </div>
                <div>
                    <span class="text-gray-600 font-semibold">Total:</span>
                    <span class="font-bold ml-1">RM {{ number_format($payroll->employer_contribution ?? 0, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Meta Info -->
        <div class="p-4 bg-gray-50 text-xs text-gray-500">
            <div class="flex justify-between">
                <span>Generated by: {{ $payroll->generator->name ?? 'System' }}</span>
                <span>Created: {{ $payroll->created_at->format('d M Y, g:i A') }}</span>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <a href="{{ route('admin.payroll.index') }}" class="text-gray-600 hover:text-gray-800 inline-flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Payroll List
        </a>

        <div class="flex gap-3">
            @if($payroll->status === 'draft')
                <form action="{{ route('admin.payroll.approve', $payroll) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Approve
                    </button>
                </form>
                <form action="{{ route('admin.payroll.destroy', $payroll) }}" method="POST" class="inline" 
                      onsubmit="return confirm('Are you sure you want to delete this payroll?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete
                    </button>
                </form>
            @endif

            @if($payroll->status === 'approved')
                <form action="{{ route('admin.payroll.paid', $payroll) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Mark as Paid
                    </button>
                </form>
            @endif

            <button onclick="window.print()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print
            </button>
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .bg-white.rounded-xl.shadow-md, .bg-white.rounded-xl.shadow-md * {
            visibility: visible;
        }
        .bg-white.rounded-xl.shadow-md {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
    }
</style>
@endsection
