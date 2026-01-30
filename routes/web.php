<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckNetworkContext;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;
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

    // Admin routes
    Route::get('admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('admin/set-ip', [AdminController::class, 'updateOfficeIp'])->name('admin.set-ip');

    // Employee management routes
    Route::get('admin/employees', [AdminController::class, 'employees'])->name('admin.employees');
    Route::get('admin/employees/create', [AdminController::class, 'createEmployee'])->name('admin.employees.create');
    Route::post('admin/employees', [AdminController::class, 'storeEmployee'])->name('admin.employees.store');
    Route::get('admin/employees/{employee}/edit', [AdminController::class, 'editEmployee'])->name('admin.employees.edit');
    Route::put('admin/employees/{employee}', [AdminController::class, 'updateEmployee'])->name('admin.employees.update');
    Route::delete('admin/employees/{employee}', [AdminController::class, 'destroyEmployee'])->name('admin.employees.destroy');
});