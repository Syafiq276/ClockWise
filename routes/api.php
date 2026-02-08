<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\ProfileController;

/*
|--------------------------------------------------------------------------
| ClockWise API Routes
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api and return JSON responses.
| Authentication is handled via Laravel Sanctum bearer tokens.
|
| Public:    POST /api/login, POST /api/register
| Protected: Everything else (requires Authorization: Bearer <token>)
|
*/

// ── Public (guest) routes ───────────────────────────────────────────────
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// ── Protected routes (Sanctum token required) ───────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // Profile
    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);
    Route::put('profile/password', [ProfileController::class, 'updatePassword']);

    // Attendance
    Route::get('attendance/today', [AttendanceController::class, 'today']);
    Route::post('attendance/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('attendance/clock-out', [AttendanceController::class, 'clockOut']);
    Route::get('attendance/history', [AttendanceController::class, 'history']);
    Route::get('attendance/stats', [AttendanceController::class, 'stats']);

    // Leave
    Route::get('leave/balances', [LeaveController::class, 'balances']);
    Route::apiResource('leave', LeaveController::class)->except(['update']);
    // DELETE /api/leave/{id} — cancel pending request

    // Payroll / Payslips
    Route::get('payslips', [PayrollController::class, 'index']);
    Route::get('payslips/{payroll}', [PayrollController::class, 'show']);
});
