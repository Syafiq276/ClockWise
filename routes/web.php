<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckNetworkContext;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AssetController;


Route::get('/', function () {
    return redirect()->route('login');
});

// Guest routes with rate limiting for security
Route::middleware(['guest', 'throttle:5,1'])->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
    
    // Password Reset
    Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'show'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');


Route::middleware(['auth', CheckNetworkContext::class])->group(function () {
    Route::get('/dashboard', function (Illuminate\Http\Request $request) {
            $attendance = App\Models\Attendance::where('user_id',$request->user()->id)
                        ->where('date',today()->toDateString())
                        ->first();

            $office_ip = App\Models\Setting::where('key','office_ip')->value('value');

            return view('dashboard',[
                'attendance' => $attendance,
                'office_ip' => $office_ip,
                'location_type' => $request->attributes->get('location_type','remote'),
                'current_ip' => $request->ip()
            ]);
        })->name('dashboard');

    Route::post('clock-in', [AttendanceController::class, 'store'])->name('clock.in');
    Route::post('clock-out', [AttendanceController::class, 'update'])->name('clock.out');
    
    // Employee attendance history
    Route::get('attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');

    // Leave request routes (Employee)
    Route::get('leave', [LeaveController::class, 'index'])->name('leave.index');
    Route::get('leave/create', [LeaveController::class, 'create'])->name('leave.create');
    Route::post('leave', [LeaveController::class, 'store'])->name('leave.store');
    Route::get('leave/{leave}', [LeaveController::class, 'show'])->name('leave.show');
    Route::delete('leave/{leave}', [LeaveController::class, 'destroy'])->name('leave.destroy');

    // Employee payslips
    Route::get('payslips', [PayrollController::class, 'myPayslips'])->name('payslips.index');
    Route::get('payslips/{payroll}', [PayrollController::class, 'viewPayslip'])->name('payslips.show');
    Route::get('payslips/{payroll}/pdf', [PayrollController::class, 'downloadPdf'])->name('payslips.pdf');

    // Asset library
    Route::get('assets/{folderId?}', [AssetController::class, 'index'])->name('assets.index');
    Route::post('assets/upload', [AssetController::class, 'store'])->name('assets.upload');
    Route::post('assets/folder', [AssetController::class, 'createFolder'])->name('assets.folder.create');
    Route::get('assets/{asset}/preview', [AssetController::class, 'preview'])->name('assets.preview');
    Route::get('assets/{asset}/download', [AssetController::class, 'download'])->name('assets.download');
    Route::delete('assets/{asset}', [AssetController::class, 'destroy'])->name('assets.destroy');

    // Admin routes - protected by admin middleware
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::post('set-ip', [AdminController::class, 'updateOfficeIp'])->name('admin.set-ip');
        Route::get('attendance', [AdminController::class, 'attendanceHistory'])->name('admin.attendance');

        // Leave management
        Route::get('leave', [AdminController::class, 'leaveRequests'])->name('admin.leave');
        Route::post('leave/{leave}/approve', [AdminController::class, 'approveLeave'])->name('admin.leave.approve');
        Route::post('leave/{leave}/reject', [AdminController::class, 'rejectLeave'])->name('admin.leave.reject');

        // Reports & analytics
        Route::get('reports', [AdminController::class, 'reports'])->name('admin.reports');
        Route::get('reports/export', [AdminController::class, 'exportReports'])->name('admin.reports.export');

        // Audit logs
        Route::get('audit-logs', [AdminController::class, 'auditLogs'])->name('admin.audit-logs');

        // Payroll management
        Route::get('payroll', [PayrollController::class, 'index'])->name('admin.payroll.index');
        Route::get('payroll/create', [PayrollController::class, 'create'])->name('admin.payroll.create');
        Route::post('payroll/calculate', [PayrollController::class, 'calculate'])->name('admin.payroll.calculate');
        Route::post('payroll', [PayrollController::class, 'store'])->name('admin.payroll.store');
        Route::get('payroll/{payroll}', [PayrollController::class, 'show'])->name('admin.payroll.show');
        Route::post('payroll/{payroll}/approve', [PayrollController::class, 'approve'])->name('admin.payroll.approve');
        Route::post('payroll/{payroll}/paid', [PayrollController::class, 'markPaid'])->name('admin.payroll.paid');
        Route::delete('payroll/{payroll}', [PayrollController::class, 'destroy'])->name('admin.payroll.destroy');

        // Employee management
        Route::get('employees', [AdminController::class, 'employees'])->name('admin.employees');
        Route::get('employees/create', [AdminController::class, 'createEmployee'])->name('admin.employees.create');
        Route::post('employees', [AdminController::class, 'storeEmployee'])->name('admin.employees.store');
        Route::get('employees/{employee}/edit', [AdminController::class, 'editEmployee'])->name('admin.employees.edit');
        Route::put('employees/{employee}', [AdminController::class, 'updateEmployee'])->name('admin.employees.update');
        Route::delete('employees/{employee}', [AdminController::class, 'destroyEmployee'])->name('admin.employees.destroy');
    });
});