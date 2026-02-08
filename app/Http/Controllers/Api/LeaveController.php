<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LeaveController extends Controller
{
    /**
     * GET /api/leave/balances
     *
     * Return leave balances for the current year.
     */
    public function balances(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'year'    => now()->year,
            'annual'  => $user->getLeaveBalance('annual'),
            'mc'      => $user->getLeaveBalance('mc'),
        ]);
    }

    /**
     * GET /api/leave
     *
     * List authenticated user's leave requests with optional filters.
     *
     * Query params: ?status=pending&type=annual&per_page=10
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = LeaveRequest::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $perPage = min((int) ($request->per_page ?? 10), 50);
        $leaves = $query->paginate($perPage);

        return response()->json([
            'data' => $leaves->map(fn ($l) => $this->formatLeave($l)),
            'meta' => [
                'current_page' => $leaves->currentPage(),
                'last_page'    => $leaves->lastPage(),
                'per_page'     => $leaves->perPage(),
                'total'        => $leaves->total(),
            ],
        ]);
    }

    /**
     * POST /api/leave
     *
     * Submit a new leave request.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type'       => ['required', 'in:annual,mc,emergency,unpaid'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'reason'     => ['required', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);

        // Calculate days
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $days = $startDate->diffInDays($endDate) + 1;

        // Check leave balance
        $user = $request->user();
        if (in_array($validated['type'], ['annual', 'mc'])) {
            if (! $user->hasLeaveBalance($validated['type'], $days)) {
                $balance = $user->getLeaveBalance($validated['type']);
                $typeName = $validated['type'] === 'annual' ? 'Annual Leave' : 'Medical Leave';
                return response()->json([
                    'message' => "Insufficient {$typeName} balance. You have {$balance['available']} days available but requested {$days} days.",
                    'balance' => $balance,
                ], 422);
            }
        }

        // Handle file upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leave-attachments', 'public');
        }

        $leave = LeaveRequest::create([
            'user_id'    => $user->id,
            'type'       => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date'   => $validated['end_date'],
            'days'       => $days,
            'reason'     => $validated['reason'],
            'attachment' => $attachmentPath,
            'status'     => 'pending',
        ]);

        return response()->json([
            'message' => 'Leave request submitted successfully.',
            'leave'   => $this->formatLeave($leave),
        ], 201);
    }

    /**
     * GET /api/leave/{id}
     *
     * View a single leave request.
     */
    public function show(LeaveRequest $leave, Request $request): JsonResponse
    {
        if ($leave->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json([
            'leave' => $this->formatLeave($leave),
        ]);
    }

    /**
     * DELETE /api/leave/{id}
     *
     * Cancel a pending leave request.
     */
    public function destroy(LeaveRequest $leave, Request $request): JsonResponse
    {
        if ($leave->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($leave->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending requests can be cancelled.',
            ], 422);
        }

        // Delete attachment if exists
        if ($leave->attachment) {
            Storage::disk('public')->delete($leave->attachment);
        }

        $leave->delete();

        return response()->json([
            'message' => 'Leave request cancelled successfully.',
        ]);
    }

    /**
     * Format a leave request for API output.
     */
    private function formatLeave(LeaveRequest $leave): array
    {
        return [
            'id'             => $leave->id,
            'type'           => $leave->type,
            'type_label'     => $leave->getTypeLabel(),
            'start_date'     => $leave->start_date->toDateString(),
            'end_date'       => $leave->end_date->toDateString(),
            'days'           => $leave->days,
            'reason'         => $leave->reason,
            'status'         => $leave->status,
            'admin_remarks'  => $leave->admin_remarks,
            'responded_at'   => $leave->responded_at?->toIso8601String(),
            'has_attachment'  => (bool) $leave->attachment,
            'created_at'     => $leave->created_at->toIso8601String(),
        ];
    }
}
