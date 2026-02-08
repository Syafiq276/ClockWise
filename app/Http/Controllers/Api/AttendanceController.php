<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * GET /api/attendance/today
     *
     * Return today's attendance record for the authenticated user.
     */
    public function today(Request $request): JsonResponse
    {
        $user = $request->user();
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', today())
            ->first();

        $officeIp = Setting::where('key', 'office_ip')->value('value');
        $currentIp = $request->ip();

        return response()->json([
            'attendance'    => $attendance ? $this->formatAttendance($attendance) : null,
            'is_clocked_in' => $attendance && ! $attendance->clock_out,
            'location_type' => ($officeIp && $currentIp === $officeIp) ? 'office' : 'remote',
            'server_time'   => now()->toIso8601String(),
        ]);
    }

    /**
     * POST /api/attendance/clock-in
     *
     * Clock in for today.
     */
    public function clockIn(Request $request): JsonResponse
    {
        $user = $request->user();
        $today = Carbon::today();
        $now = Carbon::now();

        // Check if already clocked in
        $existing = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($existing) {
            return response()->json([
                'message'    => 'You have already clocked in today.',
                'attendance' => $this->formatAttendance($existing),
            ], 409);
        }

        // Determine location
        $officeIp = Setting::where('key', 'office_ip')->value('value');
        $currentIp = $request->ip();
        $locationType = ($officeIp && $currentIp === $officeIp) ? 'office' : 'remote';

        // Determine status
        $status = $now->format('H:i:s') > '09:00:00' ? 'late' : 'ontime';

        $attendance = Attendance::create([
            'user_id'       => $user->id,
            'date'          => $today->toDateString(),
            'clock_in'      => $now->format('H:i:s'),
            'status'        => $status,
            'ip_address'    => $currentIp,
            'location_type' => $locationType,
        ]);

        return response()->json([
            'message'    => $locationType === 'office'
                ? 'Welcome to the office! Clocked in successfully.'
                : 'Clocked in from remote location successfully.',
            'attendance' => $this->formatAttendance($attendance),
        ], 201);
    }

    /**
     * POST /api/attendance/clock-out
     *
     * Clock out for today.
     */
    public function clockOut(Request $request): JsonResponse
    {
        $user = $request->user();
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', Carbon::today())
            ->first();

        if (! $attendance) {
            return response()->json([
                'message' => 'No clock-in found for today.',
            ], 404);
        }

        if ($attendance->clock_out) {
            return response()->json([
                'message'    => 'You have already clocked out today.',
                'attendance' => $this->formatAttendance($attendance),
            ], 409);
        }

        $now = Carbon::now();
        $attendance->update(['clock_out' => $now->format('H:i:s')]);
        $attendance->refresh();

        return response()->json([
            'message'    => 'Clocked out successfully.',
            'attendance' => $this->formatAttendance($attendance),
        ]);
    }

    /**
     * GET /api/attendance/history
     *
     * Paginated attendance history with optional month filter.
     *
     * Query params: ?month=2026-02&per_page=15
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Attendance::where('user_id', $user->id)
            ->orderBy('date', 'desc');

        // Filter by month (YYYY-MM format)
        if ($request->filled('month')) {
            $date = Carbon::parse($request->month . '-01');
            $query->whereYear('date', $date->year)
                  ->whereMonth('date', $date->month);
        }

        $perPage = min((int) ($request->per_page ?? 15), 50);
        $attendances = $query->paginate($perPage);

        return response()->json([
            'data' => $attendances->map(fn ($a) => $this->formatAttendance($a)),
            'meta' => [
                'current_page' => $attendances->currentPage(),
                'last_page'    => $attendances->lastPage(),
                'per_page'     => $attendances->perPage(),
                'total'        => $attendances->total(),
            ],
        ]);
    }

    /**
     * GET /api/attendance/stats
     *
     * Return summary statistics for the authenticated user.
     *
     * Query params: ?month=2026-02 (optional, defaults to all-time)
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Attendance::where('user_id', $user->id);

        if ($request->filled('month')) {
            $date = Carbon::parse($request->month . '-01');
            $query->whereYear('date', $date->year)
                  ->whereMonth('date', $date->month);
        }

        $total = (clone $query)->count();
        $onTime = (clone $query)->where('status', 'ontime')->count();
        $late   = (clone $query)->where('status', 'late')->count();

        // Total hours worked
        $allRecords = (clone $query)->whereNotNull('clock_out')->get();
        $totalMinutes = $allRecords->sum(function ($a) {
            $in  = Carbon::parse($a->clock_in);
            $out = Carbon::parse($a->clock_out);
            return $in->diffInMinutes($out);
        });

        return response()->json([
            'total_days'  => $total,
            'on_time'     => $onTime,
            'late'        => $late,
            'total_hours' => round($totalMinutes / 60, 1),
        ]);
    }

    /**
     * Format an attendance record for API output.
     */
    private function formatAttendance(Attendance $a): array
    {
        $duration = null;
        if ($a->clock_in && $a->clock_out) {
            $minutes = Carbon::parse($a->clock_in)->diffInMinutes(Carbon::parse($a->clock_out));
            $hours = floor($minutes / 60);
            $mins = $minutes % 60;
            $duration = "{$hours}h {$mins}m";
        }

        return [
            'id'            => $a->id,
            'date'          => $a->date->toDateString(),
            'day'           => $a->date->format('l'),
            'clock_in'      => $a->clock_in ? Carbon::parse($a->clock_in)->format('h:i A') : null,
            'clock_out'     => $a->clock_out ? Carbon::parse($a->clock_out)->format('h:i A') : null,
            'duration'      => $duration,
            'status'        => $a->status,
            'location_type' => $a->location_type,
            'ip_address'    => $a->ip_address,
        ];
    }
}
