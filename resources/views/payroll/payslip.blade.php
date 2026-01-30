@extends('layouts.app')

@section('title', 'Payslip - ' . \Carbon\Carbon::parse($payroll->month_year . '-01')->format('F Y'))

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Payslip</h1>
            <p class="text-gray-600">{{ \Carbon\Carbon::parse($payroll->month_year . '-01')->format('F Y') }}</p>
        </div>
        <button onclick="window.print()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition inline-flex items-center print:hidden">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print
        </button>
    </div>

    <!-- Payslip Card -->
    <div id="payslip" class="bg-white rounded-xl shadow-md overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-6">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-2xl font-bold">ClockWise</h2>
                    <p class="text-blue-100">Payslip</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-blue-100">Reference</p>
                    <p class="font-mono font-bold text-lg">#{{ str_pad($payroll->id, 6, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>
        </div>

        <!-- Employee & Period Info -->
        <div class="p-6 border-b bg-gray-50">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Employee Details</h3>
                    <p class="font-semibold text-gray-800 text-lg">{{ $payroll->user->name ?? 'N/A' }}</p>
                    <p class="text-sm text-gray-600">{{ $payroll->user->email ?? '-' }}</p>
                    <p class="text-sm text-gray-600">{{ $payroll->user->position ?? 'Staff' }}</p>
                    @if($payroll->user->employee_id ?? false)
                        <p class="text-sm text-gray-500 mt-1">ID: {{ $payroll->user->employee_id }}</p>
                    @endif
                </div>
                <div>
                    <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Pay Period</h3>
                    <p class="font-semibold text-gray-800 text-lg">{{ \Carbon\Carbon::parse($payroll->month_year . '-01')->format('F Y') }}</p>
                    @if($payroll->period_start && $payroll->period_end)
                        <p class="text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($payroll->period_start)->format('d M Y') }} - 
                            {{ \Carbon\Carbon::parse($payroll->period_end)->format('d M Y') }}
                        </p>
                    @endif
                    <p class="text-sm text-gray-600 mt-1">{{ $payroll->days_worked }} working days</p>
                </div>
            </div>
        </div>

        <!-- Work Summary -->
        <div class="p-6 border-b">
            <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-4">Work Summary</h3>
            <div class="grid grid-cols-3 gap-4 text-center">
                <div class="bg-blue-50 rounded-lg p-3">
                    <p class="text-2xl font-bold text-blue-600">{{ $payroll->days_worked }}</p>
                    <p class="text-xs text-gray-500">Days Worked</p>
                </div>
                <div class="bg-indigo-50 rounded-lg p-3">
                    <p class="text-2xl font-bold text-indigo-600">{{ $payroll->total_hours }}</p>
                    <p class="text-xs text-gray-500">Total Hours</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-3">
                    <p class="text-2xl font-bold text-purple-600">{{ $payroll->overtime_hours }}</p>
                    <p class="text-xs text-gray-500">OT Hours</p>
                </div>
            </div>
        </div>

        <!-- Earnings -->
        <div class="p-6 border-b">
            <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-4">Earnings</h3>
            <table class="w-full">
                <tbody class="divide-y divide-gray-100">
                    <tr>
                        <td class="py-3 text-gray-600">
                            Basic Pay
                            <span class="text-xs text-gray-400 block">{{ $payroll->total_hours - $payroll->overtime_hours }} hours @ RM {{ number_format($payroll->hourly_rate, 2) }}/hr</span>
                        </td>
                        <td class="py-3 text-right font-medium text-gray-800">
                            RM {{ number_format(($payroll->total_hours - $payroll->overtime_hours) * $payroll->hourly_rate, 2) }}
                        </td>
                    </tr>
                    @if($payroll->overtime_hours > 0)
                        <tr>
                            <td class="py-3 text-gray-600">
                                Overtime Pay
                                <span class="text-xs text-gray-400 block">{{ $payroll->overtime_hours }} hours @ RM {{ number_format($payroll->hourly_rate * 1.5, 2) }}/hr (1.5x)</span>
                            </td>
                            <td class="py-3 text-right font-medium text-purple-600">
                                RM {{ number_format($payroll->overtime_pay, 2) }}
                            </td>
                        </tr>
                    @endif
                    @if($payroll->allowances > 0)
                        <tr>
                            <td class="py-3 text-gray-600">
                                Allowances
                                @if($payroll->allowance_notes)
                                    <span class="text-xs text-gray-400 block">{{ $payroll->allowance_notes }}</span>
                                @endif
                            </td>
                            <td class="py-3 text-right font-medium text-green-600">
                                RM {{ number_format($payroll->allowances, 2) }}
                            </td>
                        </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-200">
                        <td class="py-3 font-semibold text-gray-800">Gross Pay</td>
                        <td class="py-3 text-right font-bold text-lg text-gray-800">
                            RM {{ number_format($payroll->gross_pay + ($payroll->allowances ?? 0), 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Deductions -->
        @if($payroll->deductions > 0)
            <div class="p-6 border-b bg-red-50">
                <h3 class="text-xs font-medium text-red-500 uppercase tracking-wider mb-4">Deductions</h3>
                <table class="w-full">
                    <tbody>
                        <tr>
                            <td class="py-2 text-gray-600">
                                Deductions
                                @if($payroll->deduction_notes)
                                    <span class="text-xs text-gray-400 block">{{ $payroll->deduction_notes }}</span>
                                @endif
                            </td>
                            <td class="py-2 text-right font-medium text-red-600">
                                - RM {{ number_format($payroll->deductions, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Net Pay -->
        <div class="p-6 bg-gradient-to-r from-green-50 to-emerald-50">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Net Pay</p>
                    <p class="text-4xl font-bold text-green-600">RM {{ number_format($payroll->net_pay, 2) }}</p>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $payroll->getStatusBadgeClass() }}">
                        {{ ucfirst($payroll->status) }}
                    </span>
                    @if($payroll->paid_at)
                        <p class="text-sm text-gray-500 mt-2">
                            Paid on {{ \Carbon\Carbon::parse($payroll->paid_at)->format('d M Y') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="p-4 bg-gray-100 text-xs text-gray-500 text-center">
            <p>This is a computer-generated payslip. No signature is required.</p>
            <p class="mt-1">Generated on {{ $payroll->created_at->format('d M Y, g:i A') }}</p>
        </div>
    </div>

    <!-- Back Link -->
    <div class="mt-6 print:hidden">
        <a href="{{ route('payslips.index') }}" class="text-blue-600 hover:text-blue-800 inline-flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Payslips
        </a>
    </div>
</div>

<style>
    @media print {
        body {
            background: white !important;
        }
        .print\:hidden {
            display: none !important;
        }
        #payslip {
            box-shadow: none !important;
            border: 1px solid #e5e7eb;
        }
    }
</style>
@endsection
