<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'hourly_rate',
        'position',
        'annual_leave_entitlement',
        'mc_entitlement',
        'employment_start_date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'employment_start_date' => 'date',
        ];
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Get leave balance for a specific type and year
     */
    public function getLeaveBalance(string $type, ?int $year = null): array
    {
        $year = $year ?? now()->year;

        $entitlement = match($type) {
            'annual' => $this->annual_leave_entitlement ?? 12,
            'mc' => $this->mc_entitlement ?? 14,
            default => 0,
        };

        $used = $this->leaveRequests()
            ->where('type', $type)
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->sum('days');

        $pending = $this->leaveRequests()
            ->where('type', $type)
            ->where('status', 'pending')
            ->whereYear('start_date', $year)
            ->sum('days');

        return [
            'entitlement' => $entitlement,
            'used' => (int) $used,
            'pending' => (int) $pending,
            'balance' => $entitlement - $used,
            'available' => $entitlement - $used - $pending, // What can still be requested
        ];
    }

    /**
     * Check if user has enough leave balance
     */
    public function hasLeaveBalance(string $type, int $days): bool
    {
        if (in_array($type, ['emergency', 'unpaid'])) {
            return true; // These don't have limits
        }

        $balance = $this->getLeaveBalance($type);
        return $balance['available'] >= $days;
    }
}
