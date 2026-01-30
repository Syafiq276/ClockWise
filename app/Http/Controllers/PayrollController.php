<?php

namespace App\Http\Controllers;

use App\Helpers\MalaysianStatutory;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Payroll;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    /**
     * Display payroll list (Admin)
     */
    public function index(Request $request)
    {
        $query = Payroll::with('user')
            ->orderBy('created_at', 'desc');

        // Filter by employee
        if ($request->filled('employee')) {
            $query->where('user_id', $request->employee);
        }

        // Filter by month
        if ($request->filled('month')) {
            $query->where('month_year', $request->month);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payrolls = $query->paginate(15)->withQueryString();
        $employees = User::where('role', 'employee')->orderBy('name')->get();

        // Stats
        $stats = [
            'total_payroll' => Payroll::where('status', 'paid')->sum('net_pay'),
            'pending_approval' => Payroll::where('status', 'draft')->count(),
            'this_month' => Payroll::where('month_year', now()->format('Y-m'))->sum('net_pay'),
        ];

        return view('admin.payroll.index', compact('payrolls', 'employees', 'stats'));
    }

    /**
     * Show generate payroll form
     */
    public function create()
    {
        $employees = User::where('role', 'employee')->orderBy('name')->get();
        
        // Default to previous month
        $defaultMonth = now()->subMonth()->format('Y-m');

        return view('admin.payroll.create', compact('employees', 'defaultMonth'));
    }

    /**
     * Calculate and preview payroll
     */
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:users,id'],
            'month_year' => ['required', 'date_format:Y-m'],
        ]);

        $employee = User::findOrFail($validated['employee_id']);
        $month = Carbon::parse($validated['month_year'] . '-01');
        $periodStart = $month->copy()->startOfMonth();
        $periodEnd = $month->copy()->endOfMonth();

        // Check if payroll already exists
        $existing = Payroll::where('user_id', $employee->id)
            ->where('month_year', $validated['month_year'])
            ->first();

        if ($existing) {
            return back()->with('error', 'Payroll for this employee and month already exists.');
        }

        // Calculate hours from attendance
        $attendances = Attendance::where('user_id', $employee->id)
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->whereNotNull('clock_out')
            ->get();

        $totalMinutes = 0;
        $overtimeMinutes = 0;
        $standardDayMinutes = 8 * 60; // 8 hours standard

        foreach ($attendances as $attendance) {
            $clockIn = Carbon::parse($attendance->clock_in);
            $clockOut = Carbon::parse($attendance->clock_out);
            $workedMinutes = $clockIn->diffInMinutes($clockOut);
            
            $totalMinutes += $workedMinutes;
            
            // Overtime: anything over 8 hours per day
            if ($workedMinutes > $standardDayMinutes) {
                $overtimeMinutes += ($workedMinutes - $standardDayMinutes);
            }
        }

        $totalHours = round($totalMinutes / 60, 2);
        $overtimeHours = round($overtimeMinutes / 60, 2);
        $regularHours = $totalHours - $overtimeHours;

        $hourlyRate = $employee->hourly_rate ?? 0;
        $overtimeRate = $hourlyRate * 1.5; // 1.5x for overtime

        $regularPay = $regularHours * $hourlyRate;
        $overtimePay = $overtimeHours * $overtimeRate;
        $grossPay = $regularPay + $overtimePay;

        // Calculate Malaysian statutory deductions
        $statutory = MalaysianStatutory::calculateAll($grossPay);

        $calculation = [
            'user_id' => $employee->id,
            'employee_name' => $employee->name,
            'month_year' => $validated['month_year'],
            'period_start' => $periodStart->format('Y-m-d'),
            'period_end' => $periodEnd->format('Y-m-d'),
            'days_worked' => $attendances->count(),
            'total_hours' => $totalHours,
            'regular_hours' => $regularHours,
            'overtime_hours' => $overtimeHours,
            'hourly_rate' => $hourlyRate,
            'overtime_rate' => $overtimeRate,
            'basic_pay' => $regularPay,
            'overtime_pay' => $overtimePay,
            'gross_pay' => $grossPay,
            // Statutory deductions
            'epf_employee' => $statutory['epf']['employee'],
            'epf_employer' => $statutory['epf']['employer'],
            'epf_rate_employee' => $statutory['epf']['employee_rate'],
            'epf_rate_employer' => $statutory['epf']['employer_rate'],
            'socso_employee' => $statutory['socso']['employee'],
            'socso_employer' => $statutory['socso']['employer'],
            'eis_employee' => $statutory['eis']['employee'],
            'eis_employer' => $statutory['eis']['employer'],
            'total_statutory' => $statutory['total_employee'],
            'employer_contribution' => $statutory['total_employer'],
        ];

        $employees = User::where('role', 'employee')->orderBy('name')->get();
        $selectedEmployee = $employee->id;
        $selectedMonth = $validated['month_year'];

        return view('admin.payroll.create', compact('employees', 'calculation', 'selectedEmployee', 'selectedMonth'));
    }

    /**
     * Store generated payroll
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'month_year' => ['required', 'date_format:Y-m'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date'],
            'days_worked' => ['required', 'integer', 'min:0'],
            'total_hours' => ['required', 'numeric', 'min:0'],
            'hourly_rate' => ['required', 'numeric', 'min:0'],
            'overtime_hours' => ['required', 'numeric', 'min:0'],
            'overtime_pay' => ['required', 'numeric', 'min:0'],
            'gross_pay' => ['required', 'numeric', 'min:0'],
            // Statutory deductions
            'epf_employee' => ['required', 'numeric', 'min:0'],
            'epf_employer' => ['required', 'numeric', 'min:0'],
            'epf_rate_employee' => ['required', 'numeric', 'min:0'],
            'epf_rate_employer' => ['required', 'numeric', 'min:0'],
            'socso_employee' => ['required', 'numeric', 'min:0'],
            'socso_employer' => ['required', 'numeric', 'min:0'],
            'eis_employee' => ['required', 'numeric', 'min:0'],
            'eis_employer' => ['required', 'numeric', 'min:0'],
            'pcb' => ['nullable', 'numeric', 'min:0'],
            'total_statutory' => ['required', 'numeric', 'min:0'],
            'employer_contribution' => ['required', 'numeric', 'min:0'],
            // Other
            'deductions' => ['nullable', 'numeric', 'min:0'],
            'deduction_notes' => ['nullable', 'string', 'max:500'],
            'allowances' => ['nullable', 'numeric', 'min:0'],
            'allowance_notes' => ['nullable', 'string', 'max:500'],
            'net_pay' => ['required', 'numeric'],
        ]);

        // Check duplicate
        $existing = Payroll::where('user_id', $validated['user_id'])
            ->where('month_year', $validated['month_year'])
            ->first();

        if ($existing) {
            return back()->with('error', 'Payroll for this employee and month already exists.');
        }

        $deductions = $validated['deductions'] ?? 0;
        $allowances = $validated['allowances'] ?? 0;
        $pcb = $validated['pcb'] ?? 0;
        $netPay = $validated['net_pay'];

        Payroll::create([
            'user_id' => $validated['user_id'],
            'month_year' => $validated['month_year'],
            'period_start' => $validated['period_start'],
            'period_end' => $validated['period_end'],
            'days_worked' => $validated['days_worked'],
            'total_hours' => $validated['total_hours'],
            'hourly_rate' => $validated['hourly_rate'],
            'overtime_hours' => $validated['overtime_hours'],
            'overtime_pay' => $validated['overtime_pay'],
            'gross_pay' => $validated['gross_pay'],
            // Statutory
            'epf_employee' => $validated['epf_employee'],
            'epf_employer' => $validated['epf_employer'],
            'epf_rate_employee' => $validated['epf_rate_employee'],
            'epf_rate_employer' => $validated['epf_rate_employer'],
            'socso_employee' => $validated['socso_employee'],
            'socso_employer' => $validated['socso_employer'],
            'eis_employee' => $validated['eis_employee'],
            'eis_employer' => $validated['eis_employer'],
            'pcb' => $pcb,
            'total_statutory' => $validated['total_statutory'],
            'employer_contribution' => $validated['employer_contribution'],
            // Other
            'deductions' => $deductions,
            'deduction_notes' => $validated['deduction_notes'],
            'allowances' => $allowances,
            'allowance_notes' => $validated['allowance_notes'],
            'net_pay' => $netPay,
            'status' => 'draft',
            'generated_by' => $request->user()->id,
        ]);

        $employee = User::find($validated['user_id']);
        AuditLog::log(
            'payroll.created',
            "Created payroll for {$employee->name} ({$validated['month_year']}) - RM " . number_format($netPay, 2),
            Payroll::class,
            $payroll->id ?? null
        );

        return redirect()->route('admin.payroll.index')
            ->with('success', 'Payroll generated successfully.');
    }

    /**
     * Show payroll details
     */
    public function show(Payroll $payroll)
    {
        $payroll->load('user', 'generator');

        return view('admin.payroll.show', compact('payroll'));
    }

    /**
     * Approve payroll
     */
    public function approve(Request $request, Payroll $payroll)
    {
        if ($payroll->status !== 'draft') {
            return back()->with('error', 'Only draft payrolls can be approved.');
        }

        $payroll->update(['status' => 'approved']);

        AuditLog::log(
            'payroll.approved',
            "Approved payroll for {$payroll->user->name} ({$payroll->month_year}) - RM " . number_format($payroll->net_pay, 2),
            Payroll::class,
            $payroll->id
        );

        return back()->with('success', 'Payroll approved successfully.');
    }

    /**
     * Mark payroll as paid
     */
    public function markPaid(Request $request, Payroll $payroll)
    {
        if ($payroll->status !== 'approved') {
            return back()->with('error', 'Only approved payrolls can be marked as paid.');
        }

        $payroll->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        AuditLog::log(
            'payroll.paid',
            "Paid payroll for {$payroll->user->name} ({$payroll->month_year}) - RM " . number_format($payroll->net_pay, 2),
            Payroll::class,
            $payroll->id
        );

        return back()->with('success', 'Payroll marked as paid.');
    }

    /**
     * Delete payroll (only draft)
     */
    public function destroy(Payroll $payroll)
    {
        if ($payroll->status !== 'draft') {
            return back()->with('error', 'Only draft payrolls can be deleted.');
        }

        $employeeName = $payroll->user->name;
        $monthYear = $payroll->month_year;
        $payrollId = $payroll->id;

        $payroll->delete();

        AuditLog::log(
            'payroll.deleted',
            "Deleted draft payroll for {$employeeName} ({$monthYear})",
            Payroll::class,
            $payrollId
        );

        return redirect()->route('admin.payroll.index')
            ->with('success', 'Payroll deleted successfully.');
    }

    /**
     * Employee view their own payslips
     */
    public function myPayslips(Request $request)
    {
        $user = $request->user();

        $payrolls = Payroll::where('user_id', $user->id)
            ->whereIn('status', ['approved', 'paid'])
            ->orderBy('period_start', 'desc')
            ->paginate(12);

        $stats = [
            'total_earned' => Payroll::where('user_id', $user->id)
                ->where('status', 'paid')->sum('net_pay'),
            'this_year' => Payroll::where('user_id', $user->id)
                ->where('status', 'paid')
                ->whereYear('period_start', now()->year)->sum('net_pay'),
        ];

        return view('payroll.my-payslips', compact('payrolls', 'stats'));
    }

    /**
     * View single payslip (employee)
     */
    public function viewPayslip(Request $request, Payroll $payroll)
    {
        $user = $request->user();
        
        // Employee can only view their own, admin can view all
        if ($user->role !== 'admin' && $payroll->user_id !== $user->id) {
            abort(403);
        }

        // Employees can only see approved/paid
        if ($user->role !== 'admin' && !in_array($payroll->status, ['approved', 'paid'])) {
            abort(404);
        }

        $payroll->load('user');

        return view('payroll.payslip', compact('payroll'));
    }

    /**
     * Download payslip as PDF
     */
    public function downloadPdf(Request $request, Payroll $payroll)
    {
        $user = $request->user();
        
        // Employee can only download their own, admin can download all
        if ($user->role !== 'admin' && $payroll->user_id !== $user->id) {
            abort(403);
        }

        // Employees can only download approved/paid
        if ($user->role !== 'admin' && !in_array($payroll->status, ['approved', 'paid'])) {
            abort(404);
        }

        $payroll->load('user');

        $pdf = Pdf::loadView('payroll.payslip-pdf', compact('payroll'));
        
        $filename = 'payslip-' . $payroll->user->name . '-' . $payroll->month_year . '.pdf';
        $filename = preg_replace('/[^A-Za-z0-9\-]/', '_', $filename);

        return $pdf->download($filename);
    }
}
