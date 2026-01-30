<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip - {{ $payroll->user->name }} - {{ \Carbon\Carbon::parse($payroll->month_year . '-01')->format('F Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        .container {
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
        }
        .payslip-title {
            font-size: 14px;
            color: #64748b;
            margin-top: 5px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .info-row {
            display: table-row;
        }
        .info-col {
            display: table-cell;
            width: 50%;
            padding: 5px 0;
            vertical-align: top;
        }
        .info-label {
            color: #64748b;
            font-size: 10px;
            text-transform: uppercase;
        }
        .info-value {
            font-weight: bold;
            color: #1e293b;
        }
        .section {
            margin-bottom: 15px;
        }
        .section-title {
            background: #f1f5f9;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 11px;
            color: #475569;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        table th {
            background: #f8fafc;
            font-weight: 600;
            color: #64748b;
            font-size: 10px;
            text-transform: uppercase;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .earnings-row td {
            color: #059669;
        }
        .deduction-row td {
            color: #dc2626;
        }
        .total-row {
            background: #f0fdf4;
            font-weight: bold;
        }
        .total-row td {
            border-top: 2px solid #10b981;
            font-size: 12px;
        }
        .net-pay-box {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: white;
            padding: 15px 20px;
            margin-top: 20px;
            border-radius: 8px;
        }
        .net-pay-label {
            font-size: 12px;
            opacity: 0.9;
        }
        .net-pay-amount {
            font-size: 28px;
            font-weight: bold;
        }
        .employer-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: 12px;
            margin-top: 15px;
            border-radius: 6px;
        }
        .employer-title {
            font-size: 10px;
            color: #3b82f6;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .employer-grid {
            display: table;
            width: 100%;
        }
        .employer-item {
            display: table-cell;
            width: 25%;
            text-align: center;
        }
        .employer-label {
            font-size: 9px;
            color: #64748b;
        }
        .employer-value {
            font-weight: bold;
            color: #1e40af;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #94a3b8;
        }
        .footer-grid {
            display: table;
            width: 100%;
        }
        .footer-col {
            display: table-cell;
            width: 50%;
        }
        .confidential {
            text-align: right;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">🕐 ClockWise</div>
            <div class="payslip-title">Payslip / Slip Gaji</div>
        </div>

        <!-- Employee & Pay Period Info -->
        <div class="info-grid">
            <div class="info-row">
                <div class="info-col">
                    <div class="info-label">Employee Name / Nama Pekerja</div>
                    <div class="info-value">{{ $payroll->user->name }}</div>
                </div>
                <div class="info-col">
                    <div class="info-label">Payroll ID</div>
                    <div class="info-value">#{{ str_pad($payroll->id, 6, '0', STR_PAD_LEFT) }}</div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-col">
                    <div class="info-label">Position / Jawatan</div>
                    <div class="info-value">{{ $payroll->user->position ?? 'Staff' }}</div>
                </div>
                <div class="info-col">
                    <div class="info-label">Pay Period / Tempoh Gaji</div>
                    <div class="info-value">{{ \Carbon\Carbon::parse($payroll->month_year . '-01')->format('F Y') }}</div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-col">
                    <div class="info-label">Email</div>
                    <div class="info-value">{{ $payroll->user->email }}</div>
                </div>
                <div class="info-col">
                    <div class="info-label">Payment Status</div>
                    <div class="info-value">{{ ucfirst($payroll->status) }}{{ $payroll->paid_at ? ' - ' . $payroll->paid_at->format('d M Y') : '' }}</div>
                </div>
            </div>
        </div>

        <!-- Work Summary -->
        <div class="section">
            <div class="section-title">Work Summary / Ringkasan Kerja</div>
            <table>
                <tr>
                    <th>Description</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-center">Rate (RM)</th>
                    <th class="text-right">Amount (RM)</th>
                </tr>
                <tr>
                    <td>Days Worked / Hari Bekerja</td>
                    <td class="text-center">{{ $payroll->days_worked }} days</td>
                    <td class="text-center">-</td>
                    <td class="text-right">-</td>
                </tr>
                <tr>
                    <td>Regular Hours / Jam Biasa</td>
                    <td class="text-center">{{ $payroll->total_hours - $payroll->overtime_hours }} hrs</td>
                    <td class="text-center">{{ number_format($payroll->hourly_rate, 2) }}</td>
                    <td class="text-right">{{ number_format(($payroll->total_hours - $payroll->overtime_hours) * $payroll->hourly_rate, 2) }}</td>
                </tr>
                @if($payroll->overtime_hours > 0)
                <tr>
                    <td>Overtime Hours / Jam Lebih Masa</td>
                    <td class="text-center">{{ $payroll->overtime_hours }} hrs</td>
                    <td class="text-center">{{ number_format($payroll->hourly_rate * 1.5, 2) }}</td>
                    <td class="text-right">{{ number_format($payroll->overtime_pay, 2) }}</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Earnings -->
        <div class="section">
            <div class="section-title">Earnings / Pendapatan</div>
            <table>
                <tr class="earnings-row">
                    <td>Basic Pay / Gaji Pokok</td>
                    <td class="text-right">{{ number_format($payroll->gross_pay - $payroll->overtime_pay, 2) }}</td>
                </tr>
                @if($payroll->overtime_pay > 0)
                <tr class="earnings-row">
                    <td>Overtime Pay / Bayaran Lebih Masa</td>
                    <td class="text-right">{{ number_format($payroll->overtime_pay, 2) }}</td>
                </tr>
                @endif
                @if(($payroll->allowances ?? 0) > 0)
                <tr class="earnings-row">
                    <td>Allowances / Elaun {{ $payroll->allowance_notes ? '(' . $payroll->allowance_notes . ')' : '' }}</td>
                    <td class="text-right">{{ number_format($payroll->allowances, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td><strong>Gross Pay / Pendapatan Kasar</strong></td>
                    <td class="text-right"><strong>{{ number_format($payroll->gross_pay + ($payroll->allowances ?? 0), 2) }}</strong></td>
                </tr>
            </table>
        </div>

        <!-- Statutory Deductions -->
        <div class="section">
            <div class="section-title">Statutory Deductions / Potongan Berkanun</div>
            <table>
                <tr class="deduction-row">
                    <td>KWSP / EPF (Employee {{ $payroll->epf_rate_employee ?? 11 }}%)</td>
                    <td class="text-right">- {{ number_format($payroll->epf_employee ?? 0, 2) }}</td>
                </tr>
                <tr class="deduction-row">
                    <td>PERKESO / SOCSO (Employee)</td>
                    <td class="text-right">- {{ number_format($payroll->socso_employee ?? 0, 2) }}</td>
                </tr>
                <tr class="deduction-row">
                    <td>SIP / EIS (Employee)</td>
                    <td class="text-right">- {{ number_format($payroll->eis_employee ?? 0, 2) }}</td>
                </tr>
                @if(($payroll->pcb ?? 0) > 0)
                <tr class="deduction-row">
                    <td>PCB / MTD (Income Tax)</td>
                    <td class="text-right">- {{ number_format($payroll->pcb, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td><strong>Total Statutory / Jumlah Potongan Berkanun</strong></td>
                    <td class="text-right"><strong>- {{ number_format($payroll->total_statutory ?? 0, 2) }}</strong></td>
                </tr>
            </table>
        </div>

        <!-- Other Deductions -->
        @if(($payroll->deductions ?? 0) > 0)
        <div class="section">
            <div class="section-title">Other Deductions / Potongan Lain</div>
            <table>
                <tr class="deduction-row">
                    <td>{{ $payroll->deduction_notes ?? 'Other Deductions' }}</td>
                    <td class="text-right">- {{ number_format($payroll->deductions, 2) }}</td>
                </tr>
            </table>
        </div>
        @endif

        <!-- Net Pay -->
        <div class="net-pay-box">
            <div class="net-pay-label">Net Pay / Gaji Bersih (Take Home)</div>
            <div class="net-pay-amount">RM {{ number_format($payroll->net_pay, 2) }}</div>
        </div>

        <!-- Employer Contribution -->
        <div class="employer-box">
            <div class="employer-title">Employer Contribution / Caruman Majikan (Not Included in Net Pay)</div>
            <div class="employer-grid">
                <div class="employer-item">
                    <div class="employer-label">EPF</div>
                    <div class="employer-value">RM {{ number_format($payroll->epf_employer ?? 0, 2) }}</div>
                </div>
                <div class="employer-item">
                    <div class="employer-label">SOCSO</div>
                    <div class="employer-value">RM {{ number_format($payroll->socso_employer ?? 0, 2) }}</div>
                </div>
                <div class="employer-item">
                    <div class="employer-label">EIS</div>
                    <div class="employer-value">RM {{ number_format($payroll->eis_employer ?? 0, 2) }}</div>
                </div>
                <div class="employer-item">
                    <div class="employer-label">Total</div>
                    <div class="employer-value">RM {{ number_format($payroll->employer_contribution ?? 0, 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-grid">
                <div class="footer-col">
                    Generated on {{ now()->format('d M Y, g:i A') }}<br>
                    ClockWise HRMS
                </div>
                <div class="footer-col confidential">
                    This is a computer-generated document.<br>
                    CONFIDENTIAL / SULIT
                </div>
            </div>
        </div>
    </div>
</body>
</html>
