<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\LeaveRequest;
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
        // Get date range (default: current month)
        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date) 
            : now()->startOfMonth();
        $endDate = $request->filled('end_date') 
            ? Carbon::parse($request->end_date) 
            : now()->endOfMonth();

        // Daily attendance data for the selected period
        $dailyAttendance = Attendance::selectRaw('date, 
                COUNT(*) as total,
                SUM(CASE WHEN status = "ontime" THEN 1 ELSE 0 END) as ontime,
                SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Location breakdown
        $locationData = Attendance::selectRaw('location_type, COUNT(*) as count')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('location_type')
            ->pluck('count', 'location_type')
            ->toArray();

        // Leave type breakdown
        $leaveData = LeaveRequest::selectRaw('type, COUNT(*) as count')
            ->where('status', 'approved')
            ->whereBetween('start_date', [$startDate, $endDate])
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        // Monthly hours per employee (top 10)
        $employeeHours = User::where('role', 'employee')
            ->withSum(['attendances as total_minutes' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate])
                    ->whereNotNull('clock_out');
            }], \DB::raw('TIMESTAMPDIFF(MINUTE, clock_in, clock_out)'))
            ->orderByDesc('total_minutes')
            ->limit(10)
            ->get()
            ->map(function ($emp) {
                return [
                    'name' => $emp->name,
                    'hours' => round(($emp->total_minutes ?? 0) / 60, 1),
                ];
            });

        // Weekly trend (last 4 weeks)
        $weeklyTrend = collect();
        for ($i = 3; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekEnd = now()->subWeeks($i)->endOfWeek();
            
            $weekData = Attendance::whereBetween('date', [$weekStart, $weekEnd])
                ->selectRaw('COUNT(*) as total, 
                    SUM(CASE WHEN status = "ontime" THEN 1 ELSE 0 END) as ontime')
                ->first();

            $weeklyTrend->push([
                'week' => $weekStart->format('d M'),
                'total' => $weekData->total ?? 0,
                'ontime' => $weekData->ontime ?? 0,
            ]);
        }

        // Summary stats
        $stats = [
            'total_attendance' => Attendance::whereBetween('date', [$startDate, $endDate])->count(),
            'unique_employees' => Attendance::whereBetween('date', [$startDate, $endDate])->distinct('user_id')->count('user_id'),
            'avg_hours' => round(Attendance::whereBetween('date', [$startDate, $endDate])
                ->whereNotNull('clock_out')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, clock_in, clock_out)) as avg')
                ->value('avg') / 60, 1) ?: 0,
            'leave_days' => LeaveRequest::where('status', 'approved')
                ->whereBetween('start_date', [$startDate, $endDate])
                ->sum('days'),
        ];

        return view('admin.reports', compact(
            'dailyAttendance', 
            'locationData', 
            'leaveData', 
            'employeeHours', 
            'weeklyTrend',
            'stats',
            'startDate',
            'endDate'
        ));
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
