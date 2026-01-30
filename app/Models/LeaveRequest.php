<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'start_date',
        'end_date',
        'days',
        'reason',
        'attachment',
        'status',
        'approved_by',
        'admin_remarks',
        'responded_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'responded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'annual' => 'Annual Leave',
            'mc' => 'Medical Leave (MC)',
            'emergency' => 'Emergency Leave',
            'unpaid' => 'Unpaid Leave',
            default => ucfirst($this->type),
        };
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'pending' => 'bg-amber-100 text-amber-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
