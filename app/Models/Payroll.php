<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = [
        'user_id',
        'month_year',
        'period_start',
        'period_end',
        'days_worked',
        'total_hours',
        'hourly_rate',
        'overtime_hours',
        'overtime_pay',
        'gross_pay',
        'deductions',
        'deduction_notes',
        'allowances',
        'allowance_notes',
        'net_pay',
        'status',
        'generated_by',
        'paid_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'paid_at' => 'datetime',
        'total_hours' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'deductions' => 'decimal:2',
        'allowances' => 'decimal:2',
        'net_pay' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'draft' => 'bg-gray-100 text-gray-800',
            'approved' => 'bg-blue-100 text-blue-800',
            'paid' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
