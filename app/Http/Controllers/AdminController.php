<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;

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
}
