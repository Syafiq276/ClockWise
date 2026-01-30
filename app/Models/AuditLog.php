<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    /**
     * Audit log for tracking security-sensitive actions.
     * 
     * Actions tracked:
     * - auth.login_success, auth.login_failed, auth.logout
     * - employee.created, employee.updated, employee.deleted
     * - payroll.created, payroll.approved, payroll.paid, payroll.deleted
     * - leave.created, leave.approved, leave.rejected
     * - settings.updated
     */

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'description',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * The user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create an audit log entry.
     */
    public static function log(
        string $action,
        ?string $description = null,
        ?string $modelType = null,
        ?int $modelId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): self {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'description' => $description,
        ]);
    }

    /**
     * Log authentication events.
     */
    public static function logAuth(string $action, ?int $userId = null, ?string $email = null): self
    {
        return self::create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $email ? "Email: {$email}" : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get recent audit logs.
     */
    public static function recent(int $limit = 100)
    {
        return self::with('user')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get logs for a specific action type.
     */
    public static function forAction(string $action)
    {
        return self::where('action', 'like', "{$action}%")
            ->with('user')
            ->orderByDesc('created_at');
    }

    /**
     * Get formatted action name.
     */
    public function getActionLabelAttribute(): string
    {
        $labels = [
            'auth.login_success' => 'Login Success',
            'auth.login_failed' => 'Login Failed',
            'auth.logout' => 'Logout',
            'employee.created' => 'Employee Created',
            'employee.updated' => 'Employee Updated',
            'employee.deleted' => 'Employee Deleted',
            'payroll.created' => 'Payroll Created',
            'payroll.approved' => 'Payroll Approved',
            'payroll.paid' => 'Payroll Paid',
            'payroll.deleted' => 'Payroll Deleted',
            'leave.created' => 'Leave Request',
            'leave.approved' => 'Leave Approved',
            'leave.rejected' => 'Leave Rejected',
            'settings.updated' => 'Settings Updated',
        ];

        return $labels[$this->action] ?? $this->action;
    }

    /**
     * Get badge color class for the action.
     */
    public function getActionBadgeClassAttribute(): string
    {
        return match (true) {
            str_contains($this->action, 'failed') => 'bg-red-100 text-red-800',
            str_contains($this->action, 'deleted') => 'bg-red-100 text-red-800',
            str_contains($this->action, 'rejected') => 'bg-red-100 text-red-800',
            str_contains($this->action, 'success') => 'bg-green-100 text-green-800',
            str_contains($this->action, 'approved') => 'bg-green-100 text-green-800',
            str_contains($this->action, 'paid') => 'bg-green-100 text-green-800',
            str_contains($this->action, 'created') => 'bg-blue-100 text-blue-800',
            str_contains($this->action, 'updated') => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
