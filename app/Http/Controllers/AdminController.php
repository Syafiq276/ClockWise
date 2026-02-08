<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $employees = User::where('role', 'employee')
            ->with(['attendances' => function ($query) {
                $query->orderByDesc('date');
            }])
            ->get();

        $employeeSummaries = $employees->map(function (User $employee) {
            $totalMinutes = $employee->attendances
                ->filter(fn (Attendance $attendance) => $attendance->clock_in && $attendance->clock_out)
                ->sum(function (Attendance $attendance) {
                    return Carbon::parse($attendance->clock_in)->diffInMinutes(Carbon::parse($attendance->clock_out));
                });

            $latest = $employee->attendances->first();

            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'position' => $employee->position,
                'total_hours' => round($totalMinutes / 60, 2),
                'last_status' => $latest?->status ?? 'n/a',
                'last_date' => $latest?->date?->toDateString(),
                'last_location' => $latest?->location_type ?? 'n/a',
            ];
        });

        $logs = Attendance::with('user')
            ->orderByDesc('date')
            ->orderByDesc('clock_in')
            ->limit(200)
            ->get();

        $officeCount = Attendance::where('location_type', 'office')->count();
        $remoteCount = Attendance::where('location_type', 'remote')->count();
        $mcCount = Attendance::where('status', 'mc')->count();
        $lateCount = Attendance::where('status', 'late')->count();
        $ontimeCount = Attendance::where('status', 'ontime')->count();

        $officeIp = Setting::where('key', 'office_ip')->value('value');

        return view('admin.dashboard', [
            'employees' => $employeeSummaries,
            'logs' => $logs,
            'office_count' => $officeCount,
            'remote_count' => $remoteCount,
            'mc_count' => $mcCount,
            'late_count' => $lateCount,
            'ontime_count' => $ontimeCount,
            'office_ip' => $officeIp,
        ]);
    }

    public function updateOfficeIp(Request $request)
    {
        $oldIp = Setting::where('key', 'office_ip')->value('value');

        Setting::updateOrCreate(
            ['key' => 'office_ip'],
            ['value' => $request->ip()]
        );

        AuditLog::log(
            'settings.updated',
            "Office IP changed from {$oldIp} to {$request->ip()}",
            Setting::class
        );

        return back()->with('success', 'Office IP updated.');
    }

    /**
     * Show all attendance records (Admin view)
     */
    public function attendanceHistory(Request $request)
    {
        $query = Attendance::with('user')
            ->orderBy('date', 'desc')
            ->orderBy('clock_in', 'desc');

        // Filter by employee
        if ($request->filled('employee')) {
            $query->where('user_id', $request->employee);
        }

        // Filter by month
        if ($request->filled('month')) {
            $date = Carbon::parse($request->month . '-01');
            $query->whereYear('date', $date->year)
                  ->whereMonth('date', $date->month);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->paginate(20)->withQueryString();
        $employees = User::where('role', 'employee')->orderBy('name')->get();

        // Summary stats
        $stats = [
            'total_records' => Attendance::count(),
            'today_present' => Attendance::whereDate('date', today())->count(),
            'this_month' => Attendance::whereYear('date', now()->year)
                                      ->whereMonth('date', now()->month)->count(),
        ];

        return view('admin.attendance.index', compact('attendances', 'employees', 'stats'));
    }

    /**
     * Employee Management - List all employees
     */
    public function employees(Request $request)
    {
        $employees = User::where('role', 'employee')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.employees.index', compact('employees'));
    }

    /**
     * Show create employee form
     */
    public function createEmployee()
    {
        return view('admin.employees.create');
    }

    /**
     * Store new employee
     */
    public function storeEmployee(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
            ],
            'position' => ['nullable', 'string', 'max:255'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        $employee = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'employee',
            'position' => $validated['position'] ?? null,
            'hourly_rate' => $validated['hourly_rate'] ?? 0,
        ]);

        AuditLog::log(
            'employee.created',
            "Created employee: {$employee->name} ({$employee->email})",
            User::class,
            $employee->id
        );

        return redirect()->route('admin.employees')
            ->with('success', 'Employee created successfully.');
    }

    /**
     * Show edit employee form
     */
    public function editEmployee(User $employee)
    {
        abort_unless($employee->role === 'employee', 404);

        return view('admin.employees.edit', compact('employee'));
    }

    /**
     * Update employee
     */
    public function updateEmployee(Request $request, User $employee)
    {
        abort_unless($employee->role === 'employee', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $employee->id],
            'password' => [
                'nullable',
                'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
            ],
            'position' => ['nullable', 'string', 'max:255'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        $oldValues = $employee->only(['name', 'email', 'position', 'hourly_rate']);

        $employee->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'position' => $validated['position'] ?? null,
            'hourly_rate' => $validated['hourly_rate'] ?? 0,
        ]);

        // Update password only if provided
        if (!empty($validated['password'])) {
            $employee->update(['password' => Hash::make($validated['password'])]);
        }

        AuditLog::log(
            'employee.updated',
            "Updated employee: {$employee->name}",
            User::class,
            $employee->id,
            $oldValues,
            $employee->only(['name', 'email', 'position', 'hourly_rate'])
        );

        return redirect()->route('admin.employees')
            ->with('success', 'Employee updated successfully.');
    }

    /**
     * Delete employee (soft delete)
     */
    public function destroyEmployee(User $employee)
    {
        abort_unless($employee->role === 'employee', 404);

        $employeeName = $employee->name;
        $employeeEmail = $employee->email;
        $employeeId = $employee->id;

        $employee->delete();

        AuditLog::log(
            'employee.deleted',
            "Deleted employee: {$employeeName} ({$employeeEmail})",
            User::class,
            $employeeId
        );

        return redirect()->route('admin.employees')
            ->with('success', 'Employee deleted successfully.');
    }

    /**
     * Display all leave requests (Admin)
     */
    public function leaveRequests(Request $request)
    {
        $query = LeaveRequest::with('user')
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by employee
        if ($request->filled('employee')) {
            $query->where('user_id', $request->employee);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $leaves = $query->paginate(15)->withQueryString();
        $employees = User::where('role', 'employee')->orderBy('name')->get();

        // Stats
        $stats = [
            'pending' => LeaveRequest::where('status', 'pending')->count(),
            'approved_this_month' => LeaveRequest::where('status', 'approved')
                ->whereMonth('responded_at', now()->month)
                ->whereYear('responded_at', now()->year)->count(),
            'total_this_month' => LeaveRequest::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count(),
        ];

        return view('admin.leave.index', compact('leaves', 'employees', 'stats'));
    }

    /**
     * Approve a leave request
     */
    public function approveLeave(Request $request, LeaveRequest $leave)
    {
        if ($leave->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        $leave->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'admin_remarks' => $request->input('remarks'),
            'responded_at' => now(),
        ]);

        AuditLog::log(
            'leave.approved',
            "Approved {$leave->type} request for {$leave->user->name}",
            LeaveRequest::class,
            $leave->id
        );

        return back()->with('success', 'Leave request approved successfully.');
    }

    /**
     * Reject a leave request
     */
    public function rejectLeave(Request $request, LeaveRequest $leave)
    {
        if ($leave->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        $validated = $request->validate([
            'remarks' => ['required', 'string', 'max:500'],
        ]);

        $leave->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->id,
            'admin_remarks' => $validated['remarks'],
            'responded_at' => now(),
        ]);

        AuditLog::log(
            'leave.rejected',
            "Rejected {$leave->type} request for {$leave->user->name}: {$validated['remarks']}",
            LeaveRequest::class,
            $leave->id
        );

        return back()->with('success', 'Leave request rejected.');
    }

    /**
     * Reports & Analytics page
     */
    public function reports(Request $request)
    {
        // ── Period presets ──────────────────────────────────────
        $preset = $request->input('preset', 'this_month');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate   = Carbon::parse($request->end_date)->endOfDay();
            $preset    = 'custom';
        } else {
            [$startDate, $endDate] = $this->resolvePresetDates($preset);
        }

        // Previous period for comparison
        $periodLength = $startDate->diffInDays($endDate) + 1;
        $prevEnd   = $startDate->copy()->subDay()->endOfDay();
        $prevStart = $prevEnd->copy()->subDays($periodLength - 1)->startOfDay();

        // ── 1. Daily attendance ────────────────────────────────
        $dailyAttendance = Attendance::selectRaw('date,
                COUNT(*) as total,
                SUM(CASE WHEN status = "ontime" THEN 1 ELSE 0 END) as ontime,
                SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // ── 2. Location breakdown ──────────────────────────────
        $locationData = Attendance::selectRaw('location_type, COUNT(*) as count')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('location_type')
            ->pluck('count', 'location_type')
            ->toArray();

        // ── 3. Leave type breakdown ────────────────────────────
        $leaveData = LeaveRequest::selectRaw('type, COUNT(*) as count')
            ->where('status', 'approved')
            ->whereBetween('start_date', [$startDate, $endDate])
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        // ── 4. Employee hours (top 10) ─────────────────────────
        $employeeHours = User::where('role', 'employee')
            ->withSum(['attendances as total_minutes' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate])
                    ->whereNotNull('clock_out');
            }], \DB::raw('TIMESTAMPDIFF(MINUTE, clock_in, clock_out)'))
            ->orderByDesc('total_minutes')
            ->limit(10)
            ->get()
            ->map(fn ($emp) => [
                'name'  => $emp->name,
                'hours' => round(($emp->total_minutes ?? 0) / 60, 1),
            ]);

        // ── 5. Weekly trend (last 4 weeks) ─────────────────────
        $weeklyTrend = collect();
        for ($i = 3; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekEnd   = now()->subWeeks($i)->endOfWeek();

            $weekData = Attendance::whereBetween('date', [$weekStart, $weekEnd])
                ->selectRaw('COUNT(*) as total,
                    SUM(CASE WHEN status = "ontime" THEN 1 ELSE 0 END) as ontime')
                ->first();

            $weeklyTrend->push([
                'week'   => $weekStart->format('d M'),
                'total'  => $weekData->total ?? 0,
                'ontime' => $weekData->ontime ?? 0,
            ]);
        }

        // ── 6. Payroll cost trend (monthly) ────────────────────
        $payrollTrend = Payroll::selectRaw('
                DATE_FORMAT(period_start, "%Y-%m") as month,
                SUM(gross_pay) as gross,
                SUM(net_pay) as net,
                SUM(total_statutory) as statutory,
                SUM(overtime_pay) as overtime')
            ->whereIn('status', ['approved', 'paid'])
            ->whereBetween('period_start', [$startDate, $endDate])
            ->groupByRaw('DATE_FORMAT(period_start, "%Y-%m")')
            ->orderBy('month')
            ->get();

        // ── 7. Employee performance scorecards ─────────────────
        $employees = User::where('role', 'employee')->get();
        $totalWorkDays = max($startDate->diffInWeekdays($endDate), 1);

        $performance = $employees->map(function (User $emp) use ($startDate, $endDate, $totalWorkDays) {
            $attendances = Attendance::where('user_id', $emp->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            $totalDays   = $attendances->count();
            $ontimeDays  = $attendances->where('status', 'ontime')->count();
            $lateDays    = $attendances->where('status', 'late')->count();
            $totalMins   = $attendances->filter(fn ($a) => $a->clock_in && $a->clock_out)
                ->sum(fn ($a) => Carbon::parse($a->clock_in)->diffInMinutes(Carbon::parse($a->clock_out)));
            $overtimeMins = max($totalMins - ($totalDays * 480), 0); // 8h = 480min baseline

            $leaveDays = LeaveRequest::where('user_id', $emp->id)
                ->where('status', 'approved')
                ->whereBetween('start_date', [$startDate, $endDate])
                ->sum('days');

            return [
                'id'               => $emp->id,
                'name'             => $emp->name,
                'position'         => $emp->position ?? '—',
                'attendance_rate'  => round(($totalDays / $totalWorkDays) * 100, 1),
                'punctuality_rate' => $totalDays > 0 ? round(($ontimeDays / $totalDays) * 100, 1) : 0,
                'total_hours'      => round($totalMins / 60, 1),
                'overtime_hours'   => round($overtimeMins / 60, 1),
                'late_days'        => $lateDays,
                'leave_days'       => $leaveDays,
            ];
        })->sortByDesc('attendance_rate')->values();

        // ── 8. Overtime trend (daily) ──────────────────────────
        $overtimeTrend = Attendance::selectRaw('date,
                SUM(GREATEST(TIMESTAMPDIFF(MINUTE, clock_in, clock_out) - 480, 0)) as ot_minutes,
                SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, clock_in, clock_out) > 480 THEN 1 ELSE 0 END) as ot_count')
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('clock_out')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date'     => Carbon::parse($row->date)->format('d M'),
                'ot_hours' => round(($row->ot_minutes ?? 0) / 60, 1),
                'ot_count' => $row->ot_count ?? 0,
            ]);

        // ── 9. Monthly leave utilisation trend ─────────────────
        $leaveTrend = LeaveRequest::selectRaw('
                DATE_FORMAT(start_date, "%Y-%m") as month,
                SUM(days) as total_days,
                SUM(CASE WHEN type = "annual" THEN days ELSE 0 END) as annual,
                SUM(CASE WHEN type = "mc" THEN days ELSE 0 END) as medical,
                SUM(CASE WHEN type = "emergency" THEN days ELSE 0 END) as emergency')
            ->where('status', 'approved')
            ->whereBetween('start_date', [$startDate, $endDate])
            ->groupByRaw('DATE_FORMAT(start_date, "%Y-%m")')
            ->orderBy('month')
            ->get();

        // ── 10. Summary stats with period comparison ───────────
        $currentTotal = Attendance::whereBetween('date', [$startDate, $endDate])->count();
        $prevTotal    = Attendance::whereBetween('date', [$prevStart, $prevEnd])->count();

        $currentOntime = Attendance::whereBetween('date', [$startDate, $endDate])->where('status', 'ontime')->count();
        $prevOntime    = Attendance::whereBetween('date', [$prevStart, $prevEnd])->where('status', 'ontime')->count();

        $avgHoursRaw = Attendance::whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('clock_out')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, clock_in, clock_out)) as avg')
            ->value('avg');
        $currentAvgHours = round(($avgHoursRaw ?? 0) / 60, 1);

        $prevAvgRaw = Attendance::whereBetween('date', [$prevStart, $prevEnd])
            ->whereNotNull('clock_out')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, clock_in, clock_out)) as avg')
            ->value('avg');
        $prevAvgHours = round(($prevAvgRaw ?? 0) / 60, 1);

        $currentLeave = LeaveRequest::where('status', 'approved')
            ->whereBetween('start_date', [$startDate, $endDate])->sum('days');
        $prevLeave = LeaveRequest::where('status', 'approved')
            ->whereBetween('start_date', [$prevStart, $prevEnd])->sum('days');

        $currentGross = Payroll::whereIn('status', ['approved', 'paid'])
            ->whereBetween('period_start', [$startDate, $endDate])->sum('gross_pay');
        $prevGross = Payroll::whereIn('status', ['approved', 'paid'])
            ->whereBetween('period_start', [$prevStart, $prevEnd])->sum('gross_pay');

        $stats = [
            'total_attendance'   => $currentTotal,
            'attendance_change'  => $prevTotal > 0 ? round((($currentTotal - $prevTotal) / $prevTotal) * 100, 1) : 0,
            'unique_employees'   => Attendance::whereBetween('date', [$startDate, $endDate])->distinct('user_id')->count('user_id'),
            'avg_hours'          => $currentAvgHours,
            'avg_hours_change'   => $prevAvgHours > 0 ? round($currentAvgHours - $prevAvgHours, 1) : 0,
            'punctuality_rate'   => $currentTotal > 0 ? round(($currentOntime / $currentTotal) * 100, 1) : 0,
            'prev_punctuality'   => $prevTotal > 0 ? round(($prevOntime / $prevTotal) * 100, 1) : 0,
            'leave_days'         => $currentLeave,
            'leave_change'       => $prevLeave > 0 ? round((($currentLeave - $prevLeave) / $prevLeave) * 100, 1) : 0,
            'total_payroll'      => $currentGross,
            'payroll_change'     => $prevGross > 0 ? round((($currentGross - $prevGross) / $prevGross) * 100, 1) : 0,
        ];

        return view('admin.reports', compact(
            'dailyAttendance',
            'locationData',
            'leaveData',
            'employeeHours',
            'weeklyTrend',
            'payrollTrend',
            'performance',
            'overtimeTrend',
            'leaveTrend',
            'stats',
            'startDate',
            'endDate',
            'preset'
        ));
    }

    /**
     * Resolve preset date ranges
     */
    private function resolvePresetDates(string $preset): array
    {
        return match ($preset) {
            'this_week'    => [now()->startOfWeek(), now()->endOfWeek()],
            'last_week'    => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            'this_month'   => [now()->startOfMonth(), now()->endOfMonth()],
            'last_month'   => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'this_quarter' => [now()->firstOfQuarter(), now()->lastOfQuarter()->endOfDay()],
            'last_quarter' => [now()->subQuarter()->firstOfQuarter(), now()->subQuarter()->lastOfQuarter()->endOfDay()],
            'q1'           => [Carbon::create(now()->year, 1, 1), Carbon::create(now()->year, 3, 31)->endOfDay()],
            'q2'           => [Carbon::create(now()->year, 4, 1), Carbon::create(now()->year, 6, 30)->endOfDay()],
            'q3'           => [Carbon::create(now()->year, 7, 1), Carbon::create(now()->year, 9, 30)->endOfDay()],
            'q4'           => [Carbon::create(now()->year, 10, 1), Carbon::create(now()->year, 12, 31)->endOfDay()],
            'this_year'    => [now()->startOfYear(), now()->endOfYear()],
            'last_year'    => [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()],
            default        => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    /**
     * Export reports data as CSV
     */
    public function exportReports(Request $request)
    {
        $preset = $request->input('preset', 'this_month');
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate   = Carbon::parse($request->end_date)->endOfDay();
        } else {
            [$startDate, $endDate] = $this->resolvePresetDates($preset);
        }

        $type = $request->input('type', 'attendance');
        $filename = "clockwise_{$type}_{$startDate->format('Ymd')}_{$endDate->format('Ymd')}.csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($type, $startDate, $endDate) {
            $file = fopen('php://output', 'w');

            switch ($type) {
                case 'attendance':
                    fputcsv($file, ['Date', 'Employee', 'Clock In', 'Clock Out', 'Status', 'Location', 'Hours']);
                    Attendance::with('user')
                        ->whereBetween('date', [$startDate, $endDate])
                        ->orderBy('date')
                        ->chunk(200, function ($rows) use ($file) {
                            foreach ($rows as $row) {
                                $hours = ($row->clock_in && $row->clock_out)
                                    ? round(Carbon::parse($row->clock_in)->diffInMinutes(Carbon::parse($row->clock_out)) / 60, 1)
                                    : 0;
                                fputcsv($file, [
                                    $row->date?->toDateString(),
                                    $row->user?->name ?? 'N/A',
                                    $row->clock_in,
                                    $row->clock_out ?? '—',
                                    ucfirst($row->status),
                                    ucfirst($row->location_type),
                                    $hours,
                                ]);
                            }
                        });
                    break;

                case 'payroll':
                    fputcsv($file, ['Month', 'Employee', 'Gross Pay', 'EPF', 'SOCSO', 'EIS', 'PCB', 'Total Statutory', 'Net Pay', 'Status']);
                    Payroll::with('user')
                        ->whereBetween('period_start', [$startDate, $endDate])
                        ->orderBy('period_start')
                        ->chunk(200, function ($rows) use ($file) {
                            foreach ($rows as $row) {
                                fputcsv($file, [
                                    $row->month_year,
                                    $row->user?->name ?? 'N/A',
                                    number_format($row->gross_pay, 2),
                                    number_format($row->epf_employee, 2),
                                    number_format($row->socso_employee, 2),
                                    number_format($row->eis_employee, 2),
                                    number_format($row->pcb, 2),
                                    number_format($row->total_statutory, 2),
                                    number_format($row->net_pay, 2),
                                    ucfirst($row->status),
                                ]);
                            }
                        });
                    break;

                case 'performance':
                    fputcsv($file, ['Employee', 'Position', 'Attendance Rate (%)', 'Punctuality (%)', 'Total Hours', 'Overtime Hours', 'Late Days', 'Leave Days']);
                    $totalWorkDays = max($startDate->diffInWeekdays($endDate), 1);

                    User::where('role', 'employee')->get()->each(function ($emp) use ($file, $startDate, $endDate, $totalWorkDays) {
                        $att = Attendance::where('user_id', $emp->id)->whereBetween('date', [$startDate, $endDate])->get();
                        $totalDays  = $att->count();
                        $ontimeDays = $att->where('status', 'ontime')->count();
                        $totalMins  = $att->filter(fn ($a) => $a->clock_in && $a->clock_out)
                            ->sum(fn ($a) => Carbon::parse($a->clock_in)->diffInMinutes(Carbon::parse($a->clock_out)));
                        $otMins     = max($totalMins - ($totalDays * 480), 0);
                        $leaveDays  = LeaveRequest::where('user_id', $emp->id)->where('status', 'approved')
                            ->whereBetween('start_date', [$startDate, $endDate])->sum('days');

                        fputcsv($file, [
                            $emp->name,
                            $emp->position ?? '—',
                            round(($totalDays / $totalWorkDays) * 100, 1),
                            $totalDays > 0 ? round(($ontimeDays / $totalDays) * 100, 1) : 0,
                            round($totalMins / 60, 1),
                            round($otMins / 60, 1),
                            $att->where('status', 'late')->count(),
                            $leaveDays,
                        ]);
                    });
                    break;
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Security Audit Logs
     */
    public function auditLogs(Request $request)
    {
        $query = AuditLog::with('user')
            ->orderBy('created_at', 'desc');

        // Filter by action type
        if ($request->filled('action')) {
            $query->where('action', 'like', $request->action . '%');
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->paginate(50)->withQueryString();
        $users = User::orderBy('name')->get();

        // Stats
        $stats = [
            'total_logs' => AuditLog::count(),
            'failed_logins_today' => AuditLog::where('action', 'auth.login_failed')
                ->whereDate('created_at', today())->count(),
            'changes_today' => AuditLog::whereDate('created_at', today())->count(),
        ];

        return view('admin.audit-logs', compact('logs', 'users', 'stats'));
    }
}
