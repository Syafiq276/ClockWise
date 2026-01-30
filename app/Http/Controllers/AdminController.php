<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'admin', 403);

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
        $user = $request->user();
        abort_unless($user && $user->role === 'admin', 403);

        Setting::updateOrCreate(
            ['key' => 'office_ip'],
            ['value' => $request->ip()]
        );

        return back()->with('success', 'Office IP updated.');
    }

    /**
     * Employee Management - List all employees
     */
    public function employees(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'admin', 403);

        $employees = User::where('role', 'employee')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.employees.index', compact('employees'));
    }

    /**
     * Show create employee form
     */
    public function createEmployee(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'admin', 403);

        return view('admin.employees.create');
    }

    /**
     * Store new employee
     */
    public function storeEmployee(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'admin', 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'position' => ['nullable', 'string', 'max:255'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'employee',
            'position' => $validated['position'] ?? null,
            'hourly_rate' => $validated['hourly_rate'] ?? 0,
        ]);

        return redirect()->route('admin.employees')
            ->with('success', 'Employee created successfully.');
    }

    /**
     * Show edit employee form
     */
    public function editEmployee(Request $request, User $employee)
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'admin', 403);
        abort_unless($employee->role === 'employee', 404);

        return view('admin.employees.edit', compact('employee'));
    }

    /**
     * Update employee
     */
    public function updateEmployee(Request $request, User $employee)
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'admin', 403);
        abort_unless($employee->role === 'employee', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $employee->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'position' => ['nullable', 'string', 'max:255'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

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

        return redirect()->route('admin.employees')
            ->with('success', 'Employee updated successfully.');
    }

    /**
     * Delete employee (soft delete)
     */
    public function destroyEmployee(Request $request, User $employee)
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'admin', 403);
        abort_unless($employee->role === 'employee', 404);

        $employee->delete();

        return redirect()->route('admin.employees')
            ->with('success', 'Employee deleted successfully.');
    }
}
