<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
class AttendanceController extends Controller
{   

public function store(Request $request)
{
    $user = Auth::user();
    $today = Carbon::today();
    $now = Carbon::now();
    $locationType = $request->attributes->get('location_type', 'remote');

    if (Attendance::where('user_id', $user->id)->where('date', $today->toDateString())->exists()) {
        return response()->json(['message' => 'You have already clocked in today.'], 400);
    }

    $status = $now->format('H:i:s') > '09:00:00' ? 'late' : 'ontime';

    Attendance::create([
        'user_id' => $user->id,
        'date' => $today->toDateString(),
        'clock_in' => $now->format('H:i:s'),
        'status' => $status,
        'ip_address' => $request->ip(),
        'location_type' => $locationType,
    ]);

    $msg = $locationType === 'office'
        ? 'Welcome to the office! You have successfully clocked in.'
        : 'You have successfully clocked in from a remote location.';

    return back()->with('success', $msg);
}

public function update()
{
    $attendance = Attendance::where('user_id', Auth::id())
        ->where('date', Carbon::today()->toDateString())
        ->first();

    if (!$attendance) {
        return back()->with('error', 'No clock-in found for today.');
    }

    if ($attendance->clock_out) {
        return back()->with('error', 'You have already clocked out today.');
    }

    $attendance->update(['clock_out' => Carbon::now()->format('H:i:s')]);

    return back()->with('success', 'You have successfully clocked out.');
}

/**
 * Show attendance history for the logged-in employee
 */
public function history(Request $request)
{
    $user = Auth::user();
    
    $query = Attendance::where('user_id', $user->id)
        ->orderBy('date', 'desc');

    // Filter by month
    if ($request->filled('month')) {
        $date = Carbon::parse($request->month . '-01');
        $query->whereYear('date', $date->year)
              ->whereMonth('date', $date->month);
    }

    $attendances = $query->paginate(15)->withQueryString();

    // Calculate summary stats for displayed records
    $stats = [
        'total_days' => $attendances->total(),
        'on_time' => Attendance::where('user_id', $user->id)->where('status', 'ontime')->count(),
        'late' => Attendance::where('user_id', $user->id)->where('status', 'late')->count(),
    ];

    return view('attendance.history', compact('attendances', 'stats'));
}
}
