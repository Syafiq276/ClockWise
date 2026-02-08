<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    // ─── Auth ───────────────────────────────────────────────────────────

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email'    => 'test@clockwise.my',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'test@clockwise.my',
            'password' => 'password123',
        ]);

        $response->assertOk()
                 ->assertJsonStructure(['message', 'token', 'user'])
                 ->assertJson(['message' => 'Login successful.']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email'    => 'test@clockwise.my',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'test@clockwise.my',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'The provided credentials are incorrect.']);
    }

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name'                  => 'New User',
            'email'                 => 'new@clockwise.my',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['message', 'token', 'user']);

        $this->assertDatabaseHas('users', [
            'email' => 'new@clockwise.my',
            'role'  => 'employee',
        ]);
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@clockwise.my']);

        $response = $this->postJson('/api/register', [
            'name'                  => 'Another',
            'email'                 => 'taken@clockwise.my',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        // Login first to get a real Sanctum token
        $loginResponse = $this->postJson('/api/login', [
            'email'    => $user->email,
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/logout');

        $response->assertOk()
                 ->assertJson(['message' => 'Logged out successfully.']);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }

    public function test_me_returns_user_data(): void
    {
        $user = User::factory()->create(['name' => 'Ahmad']);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me');

        $response->assertOk()
                 ->assertJsonPath('user.name', 'Ahmad');
    }

    // ─── Attendance ─────────────────────────────────────────────────────

    public function test_can_clock_in(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/attendance/clock-in');

        $response->assertStatus(201)
                 ->assertJsonStructure(['message', 'attendance' => ['id', 'date', 'clock_in', 'status']]);

        // Verify record exists
        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_cannot_clock_in_twice(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        // First clock-in
        $this->postJson('/api/attendance/clock-in')
             ->assertStatus(201);

        // Second clock-in should fail
        $response = $this->postJson('/api/attendance/clock-in');

        $response->assertStatus(409)
                 ->assertJson(['message' => 'You have already clocked in today.']);
    }

    public function test_can_clock_out(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        // Clock in via API first
        $this->postJson('/api/attendance/clock-in')
             ->assertStatus(201);

        $response = $this->postJson('/api/attendance/clock-out');

        $response->assertOk()
                 ->assertJson(['message' => 'Clocked out successfully.']);
    }

    public function test_clock_out_fails_without_clock_in(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/attendance/clock-out');

        $response->assertStatus(404);
    }

    public function test_today_shows_attendance_status(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        // No clock-in yet
        $response = $this->getJson('/api/attendance/today');

        $response->assertOk()
                 ->assertJson(['is_clocked_in' => false, 'attendance' => null]);
    }

    public function test_attendance_history_is_paginated(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        // Create 3 attendance records
        foreach (range(1, 3) as $i) {
            Attendance::create([
                'user_id'  => $user->id,
                'date'     => today()->subDays($i),
                'clock_in' => '09:00:00',
                'status'   => 'ontime',
            ]);
        }

        $response = $this->getJson('/api/attendance/history?per_page=2');

        $response->assertOk()
                 ->assertJsonCount(2, 'data')
                 ->assertJsonPath('meta.total', 3);
    }

    public function test_attendance_stats_returns_counts(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        Attendance::create([
            'user_id' => $user->id, 'date' => today(),
            'clock_in' => '08:30:00', 'status' => 'ontime',
        ]);
        Attendance::create([
            'user_id' => $user->id, 'date' => today()->subDay(),
            'clock_in' => '09:30:00', 'status' => 'late',
        ]);

        $response = $this->getJson('/api/attendance/stats');

        $response->assertOk()
                 ->assertJson(['total_days' => 2, 'on_time' => 1, 'late' => 1]);
    }

    // ─── Leave ──────────────────────────────────────────────────────────

    public function test_can_get_leave_balances(): void
    {
        $user = User::factory()->create([
            'annual_leave_entitlement' => 12,
            'mc_entitlement'           => 14,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/leave/balances');

        $response->assertOk()
                 ->assertJsonPath('annual.entitlement', 12)
                 ->assertJsonPath('mc.entitlement', 14);
    }

    public function test_can_submit_leave_request(): void
    {
        $user = User::factory()->create([
            'annual_leave_entitlement' => 12,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/leave', [
            'type'       => 'annual',
            'start_date' => today()->addDays(3)->toDateString(),
            'end_date'   => today()->addDays(5)->toDateString(),
            'reason'     => 'Family vacation',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('leave.type', 'annual')
                 ->assertJsonPath('leave.days', 3)
                 ->assertJsonPath('leave.status', 'pending');
    }

    public function test_leave_rejected_when_insufficient_balance(): void
    {
        $user = User::factory()->create([
            'annual_leave_entitlement' => 2,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/leave', [
            'type'       => 'annual',
            'start_date' => today()->addDay()->toDateString(),
            'end_date'   => today()->addDays(5)->toDateString(),
            'reason'     => 'Long trip',
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure(['message', 'balance']);
    }

    public function test_can_cancel_pending_leave(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $leave = LeaveRequest::create([
            'user_id'    => $user->id,
            'type'       => 'annual',
            'start_date' => today()->addWeek(),
            'end_date'   => today()->addWeek(),
            'days'       => 1,
            'reason'     => 'Personal',
            'status'     => 'pending',
        ]);

        $response = $this->deleteJson("/api/leave/{$leave->id}");

        $response->assertOk()
                 ->assertJson(['message' => 'Leave request cancelled successfully.']);

        $this->assertDatabaseMissing('leave_requests', ['id' => $leave->id]);
    }

    public function test_cannot_cancel_approved_leave(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $leave = LeaveRequest::create([
            'user_id'    => $user->id,
            'type'       => 'annual',
            'start_date' => today()->addWeek(),
            'end_date'   => today()->addWeek(),
            'days'       => 1,
            'reason'     => 'Personal',
            'status'     => 'approved',
        ]);

        $response = $this->deleteJson("/api/leave/{$leave->id}");

        $response->assertStatus(422);
    }

    public function test_cannot_view_other_users_leave(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Sanctum::actingAs($user2);

        $leave = LeaveRequest::create([
            'user_id'    => $user1->id,
            'type'       => 'annual',
            'start_date' => today()->addWeek(),
            'end_date'   => today()->addWeek(),
            'days'       => 1,
            'reason'     => 'Private',
            'status'     => 'pending',
        ]);

        $response = $this->getJson("/api/leave/{$leave->id}");

        $response->assertStatus(403);
    }

    // ─── Payslips ───────────────────────────────────────────────────────

    public function test_can_list_own_payslips(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        Payroll::create([
            'user_id'        => $user->id,
            'month_year'     => '2026-01',
            'period_start'   => '2026-01-01',
            'period_end'     => '2026-01-31',
            'gross_pay'      => 3000,
            'net_pay'        => 2500,
            'status'         => 'paid',
            'total_hours'    => 160,
            'hourly_rate'    => 18.75,
            'overtime_hours' => 0,
            'overtime_pay'   => 0,
        ]);

        $response = $this->getJson('/api/payslips');

        $response->assertOk()
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.net_pay', fn ($v) => $v == 2500);
    }

    public function test_cannot_view_other_users_payslip(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Sanctum::actingAs($user2);

        $payroll = Payroll::create([
            'user_id'        => $user1->id,
            'month_year'     => '2026-01',
            'period_start'   => '2026-01-01',
            'period_end'     => '2026-01-31',
            'gross_pay'      => 3000,
            'net_pay'        => 2500,
            'status'         => 'paid',
            'total_hours'    => 160,
            'hourly_rate'    => 18.75,
            'overtime_hours' => 0,
            'overtime_pay'   => 0,
        ]);

        $response = $this->getJson("/api/payslips/{$payroll->id}");

        $response->assertStatus(403);
    }

    // ─── Profile ────────────────────────────────────────────────────────

    public function test_can_view_profile(): void
    {
        $user = User::factory()->create(['position' => 'Developer']);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/profile');

        $response->assertOk()
                 ->assertJsonPath('profile.position', 'Developer');
    }

    public function test_can_update_profile(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/profile', ['name' => 'New Name']);

        $response->assertOk()
                 ->assertJsonPath('profile.name', 'New Name');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name']);
    }

    public function test_can_change_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpass123'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/profile/password', [
            'current_password'      => 'oldpass123',
            'password'              => 'newpass456',
            'password_confirmation' => 'newpass456',
        ]);

        $response->assertOk()
                 ->assertJson(['message' => 'Password changed successfully.']);

        // Verify new password works
        $this->assertTrue(Hash::check('newpass456', $user->fresh()->password));
    }

    public function test_password_change_fails_with_wrong_current(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('correctpass'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/profile/password', [
            'current_password'      => 'wrongpass',
            'password'              => 'newpass456',
            'password_confirmation' => 'newpass456',
        ]);

        $response->assertStatus(422);
    }
}
