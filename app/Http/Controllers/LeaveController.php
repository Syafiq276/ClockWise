<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LeaveController extends Controller
{
    /**
     * Display employee's leave requests
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = LeaveRequest::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $leaves = $query->paginate(10)->withQueryString();

        // Leave balances
        $balances = [
            'annual' => $user->getLeaveBalance('annual'),
            'mc' => $user->getLeaveBalance('mc'),
        ];

        // Stats for current year
        $currentYear = now()->year;
        $stats = [
            'total' => LeaveRequest::where('user_id', $user->id)
                ->whereYear('start_date', $currentYear)->count(),
            'approved' => LeaveRequest::where('user_id', $user->id)
                ->whereYear('start_date', $currentYear)
                ->where('status', 'approved')->sum('days'),
            'pending' => LeaveRequest::where('user_id', $user->id)
                ->where('status', 'pending')->count(),
        ];

        return view('leave.index', compact('leaves', 'stats', 'balances'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $user = Auth::user();
        
        // Get leave balances for all types
        $balances = [
            'annual' => $user->getLeaveBalance('annual'),
            'mc' => $user->getLeaveBalance('mc'),
        ];

        return view('leave.create', compact('balances'));
    }

    /**
     * Store new leave request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:annual,mc,emergency,unpaid'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);

        // Calculate days
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $days = $startDate->diffInDays($endDate) + 1;

        // Check leave balance for annual and MC
        $user = Auth::user();
        if (in_array($validated['type'], ['annual', 'mc'])) {
            if (!$user->hasLeaveBalance($validated['type'], $days)) {
                $balance = $user->getLeaveBalance($validated['type']);
                $typeName = $validated['type'] === 'annual' ? 'Annual Leave' : 'Medical Leave';
                return back()
                    ->withInput()
                    ->with('error', "Insufficient {$typeName} balance. You have {$balance['available']} days available but requested {$days} days.");
            }
        }

        // Handle file upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leave-attachments', 'public');
        }

        LeaveRequest::create([
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'days' => $days,
            'reason' => $validated['reason'],
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);

        return redirect()->route('leave.index')
            ->with('success', 'Leave request submitted successfully. Awaiting approval.');
    }

    /**
     * Show leave request details
     */
    public function show(LeaveRequest $leave)
    {
        // Ensure user can only view their own requests
        if ($leave->user_id !== Auth::id()) {
            abort(403);
        }

        return view('leave.show', compact('leave'));
    }

    /**
     * Cancel a pending leave request
     */
    public function destroy(LeaveRequest $leave)
    {
        // Ensure user can only cancel their own pending requests
        if ($leave->user_id !== Auth::id()) {
            abort(403);
        }

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be cancelled.');
        }

        // Delete attachment if exists
        if ($leave->attachment) {
            Storage::disk('public')->delete($leave->attachment);
        }

        $leave->delete();

        return redirect()->route('leave.index')
            ->with('success', 'Leave request cancelled successfully.');
    }
}
