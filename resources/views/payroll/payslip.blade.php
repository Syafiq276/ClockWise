@extends('layouts.app')

@section('title', 'Payslip - ' . \Carbon\Carbon::parse($payroll->month_year . '-01')->format('F Y'))

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <!-- Header (hidden when printing) -->
    <div class="flex justify-between items-center mb-6 print:hidden">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Payslip</h1>
            <p class="text-gray-600">{{ \Carbon\Carbon::parse($payroll->month_year . '-01')->format('F Y') }}</p>
        </div>
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition inline-flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print Payslip
        </button>
    </div>

    <!-- Payslip Card (Printable) -->
    <div id="payslip" class="bg-white rounded-xl shadow-md overflow-hidden print:shadow-none print:rounded-none">
        <!-- Company Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-6 print:bg-blue-600">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-2xl font-bold">ClockWise</h2>
                    <p class="text-blue-100 text-sm">SLIP GAJI / PAYSLIP</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-blue-100">No. Rujukan / Ref No.</p>
                    <p class="font-mono font-bold text-lg">#{{ str_pad($payroll->id, 6, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>
        </div>

        <!-- Employee & Period Info -->
        <div class="p-6 border-b bg-gray-50 print:bg-white">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Maklumat Pekerja / Employee Details</h3>
                    <p class="font-semibold text-gray-800 text-lg">{{ $payroll->user->name ?? 'N/A' }}</p>
                    <p class="text-sm text-gray-600">{{ $payroll->user->email ?? '-' }}</p>
                    <p class="text-sm text-gray-600">{{ $payroll->user->position ?? 'Staff' }}</p>
                </div>
                <div class="text-right">
                    <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Tempoh Gaji / Pay Period</h3>
                    <p class="font-semibold text-gray-800 text-lg">{{ \Carbon\Carbon::parse($payroll->month_year . '-01')->format('F Y') }}</p>
                    @if($payroll->period_start && $payroll->period_end)
                        <p class="text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($payroll->period_start)->format('d/m/Y') }} - 
                            {{ \Carbon\Carbon::parse($payroll->period_end)->format('d/m/Y') }}
                        </p>
                    @endif
                    <p class="text-sm text-gray-600">{{ $payroll->days_worked }} hari bekerja / working days</p>
                </div>
            </div>
        </div>

        <!-- Work Summary -->
        <div class="p-6 border-b">
            <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-4">Ringkasan Kerja / Work Summary</h3>
            <div class="grid grid-cols-4 gap-4 text-center">
                <div class="bg-blue-50 rounded-lg p-3 print:border print:border-gray-300">
                    <p class="text-xl font-bold text-blue-600">{{ $payroll->days_worked }}</p>
                    <p class="text-xs text-gray-500">Hari / Days</p>
                </div>
                <div class="bg-indigo-50 rounded-lg p-3 print:border print:border-gray-300">
                    <p class="text-xl font-bold text-indigo-600">{{ $payroll->total_hours }}</p>
                    <p class="text-xs text-gray-500">Jam / Hours</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-3 print:border print:border-gray-300">
                    <p class="text-xl font-bold text-purple-600">{{ $payroll->overtime_hours }}</p>
                    <p class="text-xs text-gray-500">OT (Jam)</p>
                </div>
                <div class="bg-green-50 rounded-lg p-3 print:border print:border-gray-300">
                    <p class="text-xl font-bold text-green-600">RM {{ number_format($payroll->hourly_rate, 2) }}</p>
                    <p class="text-xs text-gray-500">Kadar / Rate</p>
                </div>
            </div>
        </div>

        <!-- Earnings & Deductions Table -->
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left: Earnings -->
                <div>
                    <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3 text-green-600">Pendapatan / Earnings</h3>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-100">
                            <tr>
                                <td class="py-2 text-gray-600">Gaji Asas / Basic Pay</td>
                                <td class="py-2 text-right font-medium">{{ number_format(($payroll->total_hours - $payroll->overtime_hours) * $payroll->hourly_rate, 2) }}</td>
                            </tr>
                            @if($payroll->overtime_hours > 0)
                            <tr>
                                <td class="py-2 text-gray-600">Kerja Lebih Masa / Overtime</td>
                                <td class="py-2 text-right font-medium">{{ number_format($payroll->overtime_pay, 2) }}</td>
                            </tr>
                            @endif
                            @if($payroll->allowances > 0)
                            <tr>
                                <td class="py-2 text-gray-600">
                                    Elaun / Allowances
                                    @if($payroll->allowance_notes)
                                        <span class="text-xs text-gray-400 block">{{ $payroll->allowance_notes }}</span>
                                    @endif
                                </td>
                                <td class="py-2 text-right font-medium">{{ number_format($payroll->allowances, 2) }}</td>
                            </tr>
                            @endif
                        </tbody>
                        <tfoot class="border-t-2 border-green-200">
                            <tr class="bg-green-50 print:bg-white">
                                <td class="py-2 font-semibold text-gray-800">Jumlah Pendapatan Kasar</td>
                                <td class="py-2 text-right font-bold text-green-600">RM {{ number_format($payroll->gross_pay + ($payroll->allowances ?? 0), 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Right: Deductions -->
                <div>
                    <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3 text-red-600">Potongan / Deductions</h3>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-100">
                            <tr>
                                <td class="py-2 text-gray-600">
                                    KWSP / EPF ({{ $payroll->epf_rate_employee ?? 11 }}%)
                                </td>
                                <td class="py-2 text-right font-medium text-red-600">{{ number_format($payroll->epf_employee ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-gray-600">PERKESO / SOCSO</td>
                                <td class="py-2 text-right font-medium text-red-600">{{ number_format($payroll->socso_employee ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-gray-600">SIP / EIS</td>
                                <td class="py-2 text-right font-medium text-red-600">{{ number_format($payroll->eis_employee ?? 0, 2) }}</td>
                            </tr>
                            @if(($payroll->pcb ?? 0) > 0)
                            <tr>
                                <td class="py-2 text-gray-600">PCB / Tax</td>
                                <td class="py-2 text-right font-medium text-red-600">{{ number_format($payroll->pcb, 2) }}</td>
                            </tr>
                            @endif
                            @if(($payroll->deductions ?? 0) > 0)
                            <tr>
                                <td class="py-2 text-gray-600">
                                    Potongan Lain / Other
                                    @if($payroll->deduction_notes)
                                        <span class="text-xs text-gray-400 block">{{ $payroll->deduction_notes }}</span>
                                    @endif
                                </td>
                                <td class="py-2 text-right font-medium text-red-600">{{ number_format($payroll->deductions, 2) }}</td>
                            </tr>
                            @endif
                        </tbody>
                        <tfoot class="border-t-2 border-red-200">
                            <tr class="bg-red-50 print:bg-white">
                                <td class="py-2 font-semibold text-gray-800">Jumlah Potongan</td>
                                <td class="py-2 text-right font-bold text-red-600">RM {{ number_format(($payroll->total_statutory ?? 0) + ($payroll->deductions ?? 0), 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Net Pay -->
        <div class="p-6 bg-gradient-to-r from-green-50 to-emerald-50 print:bg-white print:border-t-4 print:border-green-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500">Gaji Bersih / Net Pay</p>
                    <p class="text-4xl font-bold text-green-600">RM {{ number_format($payroll->net_pay, 2) }}</p>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $payroll->getStatusBadgeClass() }} print:border print:border-gray-300">
                        {{ ucfirst($payroll->status) }}
                    </span>
                    @if($payroll->paid_at)
                        <p class="text-sm text-gray-500 mt-2">
                            Dibayar / Paid: {{ \Carbon\Carbon::parse($payroll->paid_at)->format('d/m/Y') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Employer Contribution (Info Box) -->
        <div class="p-4 bg-blue-50 border-t print:bg-white">
            <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Caruman Majikan / Employer Contribution</h4>
            <div class="grid grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="text-gray-600">KWSP / EPF:</span>
                    <span class="font-medium ml-1">RM {{ number_format($payroll->epf_employer ?? 0, 2) }}</span>
                </div>
                <div>
                    <span class="text-gray-600">PERKESO / SOCSO:</span>
                    <span class="font-medium ml-1">RM {{ number_format($payroll->socso_employer ?? 0, 2) }}</span>
                </div>
                <div>
                    <span class="text-gray-600">SIP / EIS:</span>
                    <span class="font-medium ml-1">RM {{ number_format($payroll->eis_employer ?? 0, 2) }}</span>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">
                Jumlah Caruman Majikan / Total Employer Contribution: 
                <span class="font-semibold">RM {{ number_format($payroll->employer_contribution ?? 0, 2) }}</span>
            </p>
        </div>

        <!-- Footer -->
        <div class="p-4 bg-gray-100 text-xs text-gray-500 text-center print:bg-white print:border-t">
            <p>Slip gaji ini dijana oleh komputer dan tidak memerlukan tandatangan.</p>
            <p>This is a computer-generated payslip. No signature is required.</p>
            <p class="mt-2">Dijana pada / Generated on: {{ $payroll->created_at->format('d/m/Y, g:i A') }}</p>
        </div>
    </div>

    <!-- Back Link (hidden when printing) -->
    <div class="mt-6 print:hidden">
        <a href="{{ route('payslips.index') }}" class="text-blue-600 hover:text-blue-800 inline-flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Senarai Slip Gaji / Back to Payslips
        </a>
    </div>
</div>

<style>
    @media print {
        body {
            background: white !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .print\:hidden {
            display: none !important;
        }
        .print\:shadow-none {
            box-shadow: none !important;
        }
        .print\:rounded-none {
            border-radius: 0 !important;
        }
        .print\:bg-white {
            background-color: white !important;
        }
        .print\:border {
            border: 1px solid #d1d5db !important;
        }
        .print\:border-gray-300 {
            border-color: #d1d5db !important;
        }
        .print\:border-t {
            border-top: 1px solid #d1d5db !important;
        }
        .print\:border-t-4 {
            border-top: 4px solid !important;
        }
        .print\:border-green-500 {
            border-top-color: #22c55e !important;
        }
        #payslip {
            width: 100%;
            max-width: 100%;
        }
        @page {
            margin: 1cm;
            size: A4;
        }
    }
</style>
@endsection
