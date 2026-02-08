<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'total_hours',
        'overtime_hours',
        'status',
        'notes',
        'ip_address',
        'location_type',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime:H:i:s',
        'clock_out' => 'datetime:H:i:s',
        'total_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
