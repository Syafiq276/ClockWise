<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    /**
     * GET /api/payslips
     *
     * List authenticated user's payslips.
     *
     * Query params: ?status=paid&year=2026&per_page=12
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Payroll::where('user_id', $user->id)
            ->whereIn('status', ['approved', 'paid'])
            ->orderBy('month_year', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('year')) {
            $query->where('month_year', 'like', $request->year . '-%');
        }

        $perPage = min((int) ($request->per_page ?? 12), 50);
        $payslips = $query->paginate($perPage);

        return response()->json([
            'data' => $payslips->map(fn ($p) => $this->formatSummary($p)),
            'meta' => [
                'current_page' => $payslips->currentPage(),
                'last_page'    => $payslips->lastPage(),
                'per_page'     => $payslips->perPage(),
                'total'        => $payslips->total(),
            ],
        ]);
    }

    /**
     * GET /api/payslips/{payroll}
     *
     * View full payslip details.
     */
    public function show(Payroll $payroll, Request $request): JsonResponse
    {
        if ($payroll->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if (! in_array($payroll->status, ['approved', 'paid'])) {
            return response()->json(['message' => 'Payslip not available yet.'], 404);
        }

        return response()->json([
            'payslip' => $this->formatDetail($payroll),
        ]);
    }

    /**
     * Format a payslip summary (for list view).
     */
    private function formatSummary(Payroll $p): array
    {
        return [
            'id'         => $p->id,
            'month_year' => $p->month_year,
            'month_label'=> Carbon::parse($p->month_year . '-01')->format('F Y'),
            'gross_pay'  => (float) $p->gross_pay,
            'net_pay'    => (float) $p->net_pay,
            'status'     => $p->status,
            'paid_at'    => $p->paid_at?->toIso8601String(),
        ];
    }

    /**
     * Format full payslip details.
     */
    private function formatDetail(Payroll $p): array
    {
        $basicPay = ($p->total_hours - $p->overtime_hours) * $p->hourly_rate;

        return [
            'id'          => $p->id,
            'month_year'  => $p->month_year,
            'month_label' => Carbon::parse($p->month_year . '-01')->format('F Y'),
            'period'      => [
                'start' => $p->period_start?->toDateString(),
                'end'   => $p->period_end?->toDateString(),
            ],
            'work' => [
                'days_worked'    => (int) $p->days_worked,
                'total_hours'    => (float) $p->total_hours,
                'overtime_hours' => (float) $p->overtime_hours,
                'hourly_rate'    => (float) $p->hourly_rate,
            ],
            'earnings' => [
                'basic_pay'    => round($basicPay, 2),
                'overtime_pay' => (float) $p->overtime_pay,
                'allowances'   => (float) ($p->allowances ?? 0),
                'gross_pay'    => (float) $p->gross_pay,
            ],
            'statutory_deductions' => [
                'epf_employee'   => (float) ($p->epf_employee ?? 0),
                'epf_rate'       => (float) ($p->epf_rate_employee ?? 11),
                'socso_employee' => (float) ($p->socso_employee ?? 0),
                'eis_employee'   => (float) ($p->eis_employee ?? 0),
                'pcb'            => (float) ($p->pcb ?? 0),
                'total'          => (float) ($p->total_statutory ?? 0),
            ],
            'other_deductions' => [
                'amount' => (float) ($p->deductions ?? 0),
                'notes'  => $p->deduction_notes,
            ],
            'net_pay'  => (float) $p->net_pay,
            'status'   => $p->status,
            'paid_at'  => $p->paid_at?->toIso8601String(),
        ];
    }
}
