# 🔧 ClockWise Technical Documentation

## System Architecture

### Overview

ClockWise is built on the **Laravel 12** framework following the MVC (Model-View-Controller) architectural pattern. The application is designed to be lightweight and deployable on shared hosting environments.

```
┌─────────────────────────────────────────────────────────────┐
│                      Client Browser                         │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    Web Server (Nginx/Apache)                │
│                    - Static files                           │
│                    - SSL termination                        │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    PHP-FPM / PHP 8.2+                       │
│  ┌───────────────────────────────────────────────────────┐  │
│  │                 Laravel Application                    │  │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐   │  │
│  │  │   Routes    │  │ Controllers │  │   Models    │   │  │
│  │  └─────────────┘  └─────────────┘  └─────────────┘   │  │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐   │  │
│  │  │    Views    │  │ Middleware  │  │   Helpers   │   │  │
│  │  └─────────────┘  └─────────────┘  └─────────────┘   │  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    MySQL 8.0 Database                       │
│                    - Users, Attendance, Leave               │
│                    - Payroll, Settings, Audit Logs          │
└─────────────────────────────────────────────────────────────┘
```

---

## Technology Stack

| Layer | Technology | Version |
|-------|------------|---------|
| Framework | Laravel | 12.x |
| Language | PHP | 8.2+ |
| Database | MySQL | 8.0 |
| Frontend | Tailwind CSS (CDN) | 3.x |
| PDF Generation | DomPDF | 3.x |
| Containerization | Docker | 24.x |

---

## Directory Structure

```
clockWise/
├── app/
│   ├── Helpers/
│   │   └── MalaysianStatutory.php      # Statutory calculations
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/                   # Admin controllers
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── EmployeeController.php
│   │   │   │   ├── AttendanceController.php
│   │   │   │   ├── LeaveController.php
│   │   │   │   ├── PayrollController.php
│   │   │   │   ├── ReportController.php
│   │   │   │   ├── SettingsController.php
│   │   │   │   └── AuditLogController.php
│   │   │   ├── Auth/                    # Authentication
│   │   │   │   ├── LoginController.php
│   │   │   │   ├── ForgotPasswordController.php
│   │   │   │   └── ResetPasswordController.php
│   │   │   ├── AttendanceController.php # Employee attendance
│   │   │   ├── DashboardController.php  # Employee dashboard
│   │   │   ├── LeaveController.php      # Employee leave
│   │   │   └── PayrollController.php    # Employee payslips
│   │   └── Middleware/
│   │       ├── Authenticate.php
│   │       └── CheckNetworkContext.php  # Office IP detection
│   ├── Models/
│   │   ├── User.php                     # User model
│   │   ├── Attendance.php               # Attendance model
│   │   ├── LeaveRequest.php             # Leave request model
│   │   ├── Payroll.php                  # Payroll model
│   │   ├── Setting.php                  # System settings
│   │   └── AuditLog.php                 # Audit logging
│   └── Providers/
│       └── AppServiceProvider.php
├── bootstrap/
│   ├── app.php
│   ├── cache/
│   └── providers.php
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── database.php
│   └── ...
├── database/
│   ├── migrations/                      # Database schema
│   └── seeders/
│       └── DatabaseSeeder.php           # Sample data
├── public/
│   ├── index.php                        # Entry point
│   └── install.php                      # Web installer
├── resources/
│   └── views/
│       ├── admin/                       # Admin views
│       ├── attendance/                  # Attendance views
│       ├── auth/                        # Auth views
│       ├── dashboard/                   # Dashboard views
│       ├── layouts/                     # Layout templates
│       │   ├── app.blade.php            # Main layout
│       │   └── guest.blade.php          # Guest layout
│       ├── leave/                       # Leave views
│       └── payroll/                     # Payroll views
├── routes/
│   ├── web.php                          # Web routes
│   └── console.php                      # CLI commands
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   └── php/
│       └── Dockerfile
├── docker-compose.yml
├── composer.json
└── .env.example
```

---

## Database Schema

### Entity Relationship Diagram

```
┌──────────────┐       ┌──────────────────┐       ┌──────────────────┐
│    users     │       │   attendances    │       │  leave_requests  │
├──────────────┤       ├──────────────────┤       ├──────────────────┤
│ id (PK)      │◄──────│ user_id (FK)     │       │ id (PK)          │
│ name         │       │ id (PK)          │       │ user_id (FK)     │──┐
│ email        │       │ date             │       │ type             │  │
│ password     │       │ clock_in         │       │ start_date       │  │
│ role         │       │ clock_out        │       │ end_date         │  │
│ position     │       │ status           │       │ days             │  │
│ hourly_rate  │       │ location_type    │       │ reason           │  │
│ annual_leave │       │ ip_address       │       │ status           │  │
│ mc_entitle.. │       │ created_at       │       │ approved_by (FK) │──┼─┐
│ employ_start │       │ updated_at       │       │ admin_remarks    │  │ │
│ created_at   │       └──────────────────┘       │ responded_at     │  │ │
│ updated_at   │                                  │ created_at       │  │ │
└──────────────┘                                  └──────────────────┘  │ │
       │                                                    ▲          │ │
       │         ┌──────────────────┐                       │          │ │
       │         │     payrolls     │                       └──────────┘ │
       │         ├──────────────────┤                                    │
       └────────►│ user_id (FK)     │◄───────────────────────────────────┘
                 │ id (PK)          │
                 │ month_year       │       ┌──────────────────┐
                 │ period_start     │       │   audit_logs     │
                 │ period_end       │       ├──────────────────┤
                 │ days_worked      │       │ id (PK)          │
                 │ total_hours      │       │ user_id (FK)     │
                 │ hourly_rate      │       │ action           │
                 │ overtime_hours   │       │ model_type       │
                 │ overtime_pay     │       │ model_id         │
                 │ gross_pay        │       │ old_values       │
                 │ epf_employee     │       │ new_values       │
                 │ epf_employer     │       │ ip_address       │
                 │ socso_employee   │       │ user_agent       │
                 │ socso_employer   │       │ description      │
                 │ eis_employee     │       │ created_at       │
                 │ eis_employer     │       │ updated_at       │
                 │ net_pay          │       └──────────────────┘
                 │ status           │
                 │ generated_by     │       ┌──────────────────┐
                 │ paid_at          │       │    settings      │
                 │ created_at       │       ├──────────────────┤
                 │ updated_at       │       │ id (PK)          │
                 └──────────────────┘       │ key (unique)     │
                                            │ value            │
                                            │ created_at       │
                                            │ updated_at       │
                                            └──────────────────┘
```

### Table Definitions

#### users
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'employee') DEFAULT 'employee',
    position VARCHAR(255) NULL,
    hourly_rate DECIMAL(8,2) DEFAULT 0.00,
    annual_leave_entitlement INT DEFAULT 12,
    mc_entitlement INT DEFAULT 14,
    employment_start_date DATE NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

#### attendances
```sql
CREATE TABLE attendances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    clock_in TIME NOT NULL,
    clock_out TIME NULL,
    status VARCHAR(255) DEFAULT 'ontime',
    ip_address VARCHAR(45) NULL,
    location_type VARCHAR(255) DEFAULT 'remote',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (user_id, date)
);
```

#### leave_requests
```sql
CREATE TABLE leave_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type ENUM('annual', 'mc', 'emergency', 'unpaid') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    days INT NOT NULL,
    reason TEXT NOT NULL,
    attachment VARCHAR(255) NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by BIGINT UNSIGNED NULL,
    admin_remarks TEXT NULL,
    responded_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);
```

#### payrolls
```sql
CREATE TABLE payrolls (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    month_year VARCHAR(7) NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    days_worked INT DEFAULT 0,
    total_hours DECIMAL(8,2) DEFAULT 0.00,
    hourly_rate DECIMAL(8,2) DEFAULT 0.00,
    overtime_hours DECIMAL(8,2) DEFAULT 0.00,
    overtime_pay DECIMAL(10,2) DEFAULT 0.00,
    gross_pay DECIMAL(10,2) NOT NULL,
    epf_employee DECIMAL(10,2) DEFAULT 0.00,
    epf_employer DECIMAL(10,2) DEFAULT 0.00,
    epf_rate_employee DECIMAL(5,2) DEFAULT 11.00,
    epf_rate_employer DECIMAL(5,2) DEFAULT 13.00,
    socso_employee DECIMAL(10,2) DEFAULT 0.00,
    socso_employer DECIMAL(10,2) DEFAULT 0.00,
    eis_employee DECIMAL(10,2) DEFAULT 0.00,
    eis_employer DECIMAL(10,2) DEFAULT 0.00,
    pcb DECIMAL(10,2) DEFAULT 0.00,
    total_statutory DECIMAL(10,2) DEFAULT 0.00,
    employer_contribution DECIMAL(10,2) DEFAULT 0.00,
    deductions DECIMAL(10,2) DEFAULT 0.00,
    deduction_notes TEXT NULL,
    allowances DECIMAL(10,2) DEFAULT 0.00,
    allowance_notes TEXT NULL,
    net_pay DECIMAL(10,2) NOT NULL,
    status ENUM('draft', 'approved', 'paid') DEFAULT 'draft',
    generated_by BIGINT UNSIGNED NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_payroll (user_id, month_year)
);
```

---

## Core Components

### 1. Malaysian Statutory Helper

**File:** `app/Helpers/MalaysianStatutory.php`

Handles all Malaysian statutory calculations:

```php
class MalaysianStatutory
{
    // EPF Calculation
    public static function calculateEPF(float $salary, int $age = 30): array
    {
        $employeeRate = $age < 60 ? 0.11 : 0.055;
        $employerRate = $age < 60 ? 0.13 : 0.04;
        
        return [
            'employee' => round($salary * $employeeRate, 2),
            'employer' => round($salary * $employerRate, 2),
        ];
    }
    
    // SOCSO Calculation (with caps)
    public static function calculateSOCSO(float $salary): array
    {
        // Based on SOCSO contribution table
        // Returns capped amounts
    }
    
    // EIS Calculation
    public static function calculateEIS(float $salary): array
    {
        $rate = 0.002;
        $cap = 39.90;
        
        return [
            'employee' => min($salary * $rate, $cap),
            'employer' => min($salary * $rate, $cap),
        ];
    }
}
```

### 2. Audit Logging

**File:** `app/Models/AuditLog.php`

Tracks all security-sensitive actions:

```php
// Creating an audit log
AuditLog::log(
    action: 'employee.created',
    description: 'New employee created: John Doe',
    modelType: User::class,
    modelId: $user->id,
    newValues: $user->toArray()
);

// Tracked actions
- auth.login_success
- auth.login_failed
- auth.logout
- employee.created
- employee.updated
- employee.deleted
- leave.created
- leave.approved
- leave.rejected
- payroll.created
- payroll.approved
- payroll.paid
- settings.updated
```

### 3. Attendance Controller

**File:** `app/Http/Controllers/AttendanceController.php`

```php
public function clockIn(Request $request)
{
    $user = auth()->user();
    $today = Carbon::today();
    
    // Check if already clocked in
    $attendance = Attendance::where('user_id', $user->id)
        ->where('date', $today)
        ->first();
    
    if ($attendance) {
        return back()->with('error', 'Already clocked in today');
    }
    
    // Determine status (late if after 9:15 AM)
    $status = Carbon::now()->gt(Carbon::today()->setTime(9, 15)) 
        ? 'late' 
        : 'ontime';
    
    // Create attendance record
    Attendance::create([
        'user_id' => $user->id,
        'date' => $today,
        'clock_in' => Carbon::now(),
        'status' => $status,
        'ip_address' => $request->ip(),
        'location_type' => $this->detectLocation($request->ip()),
    ]);
    
    return back()->with('success', 'Clocked in successfully');
}
```

### 4. Payroll Generation

**File:** `app/Http/Controllers/Admin/PayrollController.php`

```php
public function generate(Request $request)
{
    $monthYear = $request->month_year; // Format: 2026-01
    $employees = User::where('role', 'employee')->get();
    
    foreach ($employees as $employee) {
        // Calculate hours from attendance
        $attendance = Attendance::where('user_id', $employee->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();
        
        $totalHours = $attendance->sum(function ($a) {
            return Carbon::parse($a->clock_out)
                ->diffInHours(Carbon::parse($a->clock_in));
        });
        
        // Calculate pay
        $basicPay = $totalHours * $employee->hourly_rate;
        $overtimePay = $overtimeHours * $employee->hourly_rate * 1.5;
        $grossPay = $basicPay + $overtimePay;
        
        // Calculate deductions
        $epf = MalaysianStatutory::calculateEPF($grossPay);
        $socso = MalaysianStatutory::calculateSOCSO($grossPay);
        $eis = MalaysianStatutory::calculateEIS($grossPay);
        
        $totalDeductions = $epf['employee'] + $socso['employee'] + $eis['employee'];
        $netPay = $grossPay - $totalDeductions;
        
        // Create payroll record
        Payroll::create([...]);
    }
}
```

---

## Authentication Flow

### Login Process

```
User submits credentials
        │
        ▼
┌─────────────────────┐
│ LoginController     │
│ - Validate input    │
│ - Attempt auth      │
└─────────────────────┘
        │
        ▼
┌─────────────────────┐     ┌─────────────────────┐
│ Auth successful?    │─No─▶│ Return with error   │
└─────────────────────┘     │ Log failed attempt  │
        │ Yes               └─────────────────────┘
        ▼
┌─────────────────────┐
│ Create session      │
│ Log successful auth │
│ Redirect to dash    │
└─────────────────────┘
```

### Password Reset Flow

```
User requests reset
        │
        ▼
┌─────────────────────┐
│ Generate token      │
│ Store in database   │
│ Send email          │
└─────────────────────┘
        │
        ▼
User clicks email link
        │
        ▼
┌─────────────────────┐
│ Validate token      │
│ Show reset form     │
└─────────────────────┘
        │
        ▼
User submits new password
        │
        ▼
┌─────────────────────┐
│ Update password     │
│ Delete token        │
│ Redirect to login   │
└─────────────────────┘
```

---

## Middleware

### Authentication Middleware

```php
// routes/web.php
Route::middleware(['auth'])->group(function () {
    // Protected routes
});
```

### Admin Middleware

```php
// Check if user is admin
Route::middleware(['auth'])->prefix('admin')->group(function () {
    // Handled in controllers with role check
    if (auth()->user()->role !== 'admin') {
        abort(403);
    }
});
```

### Network Context Middleware

```php
// app/Http/Middleware/CheckNetworkContext.php
public function handle($request, Closure $next)
{
    $officeIp = Setting::get('office_ip');
    $request->merge([
        'is_office' => $request->ip() === $officeIp
    ]);
    
    return $next($request);
}
```

---

## API Endpoints

### Employee Endpoints

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | /dashboard | DashboardController@index | Dashboard |
| POST | /attendance/clock-in | AttendanceController@clockIn | Clock in |
| POST | /attendance/clock-out | AttendanceController@clockOut | Clock out |
| GET | /attendance/history | AttendanceController@history | History |
| GET | /leave | LeaveController@index | Leave list |
| POST | /leave | LeaveController@store | Submit leave |
| GET | /payslips | PayrollController@myPayslips | My payslips |
| GET | /payslips/{id}/download | PayrollController@downloadPdf | Download PDF |

### Admin Endpoints

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | /admin | Admin\DashboardController@index | Overview |
| GET | /admin/employees | Admin\EmployeeController@index | List |
| POST | /admin/employees | Admin\EmployeeController@store | Create |
| PUT | /admin/employees/{id} | Admin\EmployeeController@update | Update |
| DELETE | /admin/employees/{id} | Admin\EmployeeController@destroy | Delete |
| POST | /admin/leave/{id}/approve | Admin\LeaveController@approve | Approve |
| POST | /admin/leave/{id}/reject | Admin\LeaveController@reject | Reject |
| POST | /admin/payroll/generate | Admin\PayrollController@generate | Generate |

---

## Environment Configuration

### Development

```env
APP_ENV=local
APP_DEBUG=true
DB_HOST=127.0.0.1
```

### Production

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Optimization
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

### Docker

```env
DB_HOST=db
DB_DATABASE=clockwise
DB_USERNAME=clockwise
DB_PASSWORD=clockwise
```

---

## Performance Optimization

### Caching

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache
```

### Database Optimization

```sql
-- Indexes are automatically created on:
-- - users.email (unique)
-- - attendances.user_id, attendances.date (composite)
-- - leave_requests.user_id, leave_requests.status
-- - payrolls.user_id, payrolls.month_year (composite unique)
```

### Autoloader Optimization

```bash
composer install --optimize-autoloader --no-dev
```

---

## Security Considerations

### Input Validation

All user inputs are validated:

```php
$request->validate([
    'email' => 'required|email|unique:users',
    'password' => 'required|min:8',
    'hourly_rate' => 'required|numeric|min:0',
]);
```

### CSRF Protection

All forms include CSRF token:

```blade
<form method="POST">
    @csrf
    ...
</form>
```

### SQL Injection Prevention

Using Eloquent ORM and query builder:

```php
// Safe - using Eloquent
User::where('email', $email)->first();

// Safe - using query builder with bindings
DB::select('SELECT * FROM users WHERE email = ?', [$email]);
```

### XSS Prevention

Blade templates auto-escape:

```blade
{{ $user->name }}  <!-- Escaped -->
{!! $html !!}      <!-- Raw - use with caution -->
```

---

## Logging

### Application Logs

Location: `storage/logs/laravel.log`

```php
Log::info('User logged in', ['user_id' => $user->id]);
Log::error('Payment failed', ['error' => $e->getMessage()]);
```

### Audit Logs

Stored in database for compliance and security review.

---

## Deployment Checklist

- [ ] Set APP_ENV=production
- [ ] Set APP_DEBUG=false
- [ ] Generate new APP_KEY
- [ ] Configure database
- [ ] Set up email (SMTP)
- [ ] Run composer install --no-dev
- [ ] Run php artisan config:cache
- [ ] Run php artisan route:cache
- [ ] Run php artisan view:cache
- [ ] Set storage permissions (775)
- [ ] Configure HTTPS/SSL
- [ ] Set up scheduled tasks (optional)
- [ ] Configure backups

---

## Troubleshooting

### Common Issues

| Issue | Solution |
|-------|----------|
| 500 Error | Check storage/logs/laravel.log |
| Database connection | Verify .env credentials |
| Permission denied | chmod -R 775 storage bootstrap/cache |
| Class not found | composer dump-autoload |
| Route not found | php artisan route:clear |

### Debug Mode

Temporarily enable for troubleshooting:

```env
APP_DEBUG=true
```

**Remember to disable in production!**

---

<p align="center">
  <em>ClockWise Technical Documentation v1.0</em><br>
  <em>Last Updated: January 2026</em>
</p>
