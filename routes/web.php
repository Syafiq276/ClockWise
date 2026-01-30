<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckNetworkContext;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;


Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
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

    // Admin routes
    Route::get('admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('admin/set-ip', [AdminController::class, 'updateOfficeIp'])->name('admin.set-ip');
    Route::get('admin/attendance', [AdminController::class, 'attendanceHistory'])->name('admin.attendance');

    // Admin leave management routes
    Route::get('admin/leave', [AdminController::class, 'leaveRequests'])->name('admin.leave');
    Route::post('admin/leave/{leave}/approve', [AdminController::class, 'approveLeave'])->name('admin.leave.approve');
    Route::post('admin/leave/{leave}/reject', [AdminController::class, 'rejectLeave'])->name('admin.leave.reject');

    // Admin reports & analytics
    Route::get('admin/reports', [AdminController::class, 'reports'])->name('admin.reports');

    // Admin payroll management
    Route::get('admin/payroll', [PayrollController::class, 'index'])->name('admin.payroll.index');
    Route::get('admin/payroll/create', [PayrollController::class, 'create'])->name('admin.payroll.create');
    Route::post('admin/payroll/calculate', [PayrollController::class, 'calculate'])->name('admin.payroll.calculate');
    Route::post('admin/payroll', [PayrollController::class, 'store'])->name('admin.payroll.store');
    Route::get('admin/payroll/{payroll}', [PayrollController::class, 'show'])->name('admin.payroll.show');
    Route::post('admin/payroll/{payroll}/approve', [PayrollController::class, 'approve'])->name('admin.payroll.approve');
    Route::post('admin/payroll/{payroll}/paid', [PayrollController::class, 'markPaid'])->name('admin.payroll.paid');
    Route::delete('admin/payroll/{payroll}', [PayrollController::class, 'destroy'])->name('admin.payroll.destroy');

    // Employee management routes
    Route::get('admin/employees', [AdminController::class, 'employees'])->name('admin.employees');
    Route::get('admin/employees/create', [AdminController::class, 'createEmployee'])->name('admin.employees.create');
    Route::post('admin/employees', [AdminController::class, 'storeEmployee'])->name('admin.employees.store');
    Route::get('admin/employees/{employee}/edit', [AdminController::class, 'editEmployee'])->name('admin.employees.edit');
    Route::put('admin/employees/{employee}', [AdminController::class, 'updateEmployee'])->name('admin.employees.update');
    Route::delete('admin/employees/{employee}', [AdminController::class, 'destroyEmployee'])->name('admin.employees.destroy');
});