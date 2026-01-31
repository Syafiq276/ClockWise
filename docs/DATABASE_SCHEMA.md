# 🗄️ ClockWise Database Schema

> Complete database structure and relationships

---

## Overview

ClockWise uses **MySQL 8.0** as its database engine. The schema is designed to support:

- User and employee management
- Attendance tracking
- Leave management
- Payroll processing
- Audit logging
- System configuration

---

## Tables Summary

| Table | Description | Records (Seeded) |
|-------|-------------|------------------|
| users | User accounts | 6 |
| attendances | Clock in/out records | 103 |
| leave_requests | Leave applications | 8 |
| payrolls | Monthly payroll | 15 |
| settings | System configuration | 11 |
| audit_logs | Activity tracking | 76+ |
| password_reset_tokens | Password resets | 0 |
| sessions | Active sessions | Dynamic |
| cache | Application cache | Dynamic |
| jobs | Queue jobs | 0 |

---

## Entity Relationship Diagram

```
┌─────────────────────────────────────────────────────────────────────────┐
│                              USERS                                       │
│  ┌─────────────────────────────────────────────────────────────────┐    │
│  │ id | name | email | password | role | position | hourly_rate    │    │
│  │ annual_leave_entitlement | mc_entitlement | employment_start    │    │
│  └─────────────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────────────┘
         │              │              │              │
         │              │              │              │
         ▼              ▼              ▼              ▼
    ATTENDANCES   LEAVE_REQUESTS    PAYROLLS    AUDIT_LOGS
         │              │              │
         │              │              │
         └──────────────┴──────────────┘
                        │
                        ▼
                    SETTINGS
                  (standalone)
```

---

## Table: users

**Purpose:** Store user account information and employee details

```sql
CREATE TABLE `users` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `email_verified_at` timestamp NULL DEFAULT NULL,
    `password` varchar(255) NOT NULL,
    `role` enum('admin','employee') DEFAULT 'employee',
    `position` varchar(255) DEFAULT NULL,
    `hourly_rate` decimal(8,2) DEFAULT '0.00',
    `annual_leave_entitlement` int DEFAULT '12',
    `mc_entitlement` int DEFAULT '14',
    `employment_start_date` date DEFAULT NULL,
    `remember_token` varchar(100) DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Columns

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint unsigned | No | AUTO | Primary key |
| name | varchar(255) | No | - | Full name |
| email | varchar(255) | No | - | Login email (unique) |
| email_verified_at | timestamp | Yes | NULL | Email verification |
| password | varchar(255) | No | - | Hashed password |
| role | enum | No | 'employee' | admin or employee |
| position | varchar(255) | Yes | NULL | Job title |
| hourly_rate | decimal(8,2) | No | 0.00 | Pay rate (RM/hour) |
| annual_leave_entitlement | int | No | 12 | Annual leave days |
| mc_entitlement | int | No | 14 | MC days |
| employment_start_date | date | Yes | NULL | Start date |
| remember_token | varchar(100) | Yes | NULL | Session token |
| created_at | timestamp | Yes | NULL | Created time |
| updated_at | timestamp | Yes | NULL | Updated time |

### Indexes
- **PRIMARY**: `id`
- **UNIQUE**: `email`

### Sample Data
```sql
INSERT INTO users (name, email, password, role, position, hourly_rate, annual_leave_entitlement, mc_entitlement, employment_start_date) VALUES
('Admin', 'admin@clockwise.my', '$2y$12$...', 'admin', 'System Administrator', 75.00, 18, 18, '2024-01-01'),
('Ali bin Abu', 'ali@clockwise.my', '$2y$12$...', 'employee', 'Software Developer', 35.00, 14, 14, '2024-03-15');
```

---

## Table: attendances

**Purpose:** Track daily clock in/out records

```sql
CREATE TABLE `attendances` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint unsigned NOT NULL,
    `date` date NOT NULL,
    `clock_in` time NOT NULL,
    `clock_out` time DEFAULT NULL,
    `status` varchar(255) DEFAULT 'ontime',
    `ip_address` varchar(45) DEFAULT NULL,
    `location_type` varchar(255) DEFAULT 'remote',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `attendances_user_date_unique` (`user_id`,`date`),
    KEY `attendances_user_id_index` (`user_id`),
    CONSTRAINT `attendances_user_id_foreign` 
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Columns

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint unsigned | No | AUTO | Primary key |
| user_id | bigint unsigned | No | - | FK to users |
| date | date | No | - | Attendance date |
| clock_in | time | No | - | Clock in time |
| clock_out | time | Yes | NULL | Clock out time |
| status | varchar(255) | No | 'ontime' | ontime/late |
| ip_address | varchar(45) | Yes | NULL | Client IP |
| location_type | varchar(255) | No | 'remote' | office/remote |
| created_at | timestamp | Yes | NULL | Created time |
| updated_at | timestamp | Yes | NULL | Updated time |

### Indexes
- **PRIMARY**: `id`
- **UNIQUE**: `user_id`, `date` (composite)
- **INDEX**: `user_id`

### Foreign Keys
- `user_id` → `users.id` (CASCADE on delete)

### Status Values
| Value | Description |
|-------|-------------|
| ontime | Clocked in before 9:15 AM |
| late | Clocked in after 9:15 AM |

### Sample Data
```sql
INSERT INTO attendances (user_id, date, clock_in, clock_out, status, ip_address, location_type) VALUES
(2, '2026-01-30', '08:55:00', '18:05:00', 'ontime', '192.168.1.100', 'office'),
(2, '2026-01-29', '09:20:00', '18:30:00', 'late', '203.106.45.2', 'remote');
```

---

## Table: leave_requests

**Purpose:** Store leave applications and approvals

```sql
CREATE TABLE `leave_requests` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint unsigned NOT NULL,
    `type` enum('annual','mc','emergency','unpaid') NOT NULL,
    `start_date` date NOT NULL,
    `end_date` date NOT NULL,
    `days` int NOT NULL,
    `reason` text NOT NULL,
    `attachment` varchar(255) DEFAULT NULL,
    `status` enum('pending','approved','rejected') DEFAULT 'pending',
    `approved_by` bigint unsigned DEFAULT NULL,
    `admin_remarks` text,
    `responded_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `leave_requests_user_id_index` (`user_id`),
    KEY `leave_requests_approved_by_index` (`approved_by`),
    CONSTRAINT `leave_requests_user_id_foreign` 
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `leave_requests_approved_by_foreign` 
        FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Columns

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint unsigned | No | AUTO | Primary key |
| user_id | bigint unsigned | No | - | FK to users |
| type | enum | No | - | Leave type |
| start_date | date | No | - | Start date |
| end_date | date | No | - | End date |
| days | int | No | - | Number of days |
| reason | text | No | - | Reason for leave |
| attachment | varchar(255) | Yes | NULL | File path |
| status | enum | No | 'pending' | Request status |
| approved_by | bigint unsigned | Yes | NULL | Admin FK |
| admin_remarks | text | Yes | NULL | Admin notes |
| responded_at | timestamp | Yes | NULL | Response time |
| created_at | timestamp | Yes | NULL | Created time |
| updated_at | timestamp | Yes | NULL | Updated time |

### Leave Types
| Type | Description | Deducts From |
|------|-------------|--------------|
| annual | Annual leave | annual_leave_entitlement |
| mc | Medical certificate | mc_entitlement |
| emergency | Emergency leave | annual_leave_entitlement |
| unpaid | Unpaid leave | None |

### Status Values
| Status | Description |
|--------|-------------|
| pending | Awaiting approval |
| approved | Leave granted |
| rejected | Leave denied |

---

## Table: payrolls

**Purpose:** Store monthly payroll calculations

```sql
CREATE TABLE `payrolls` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint unsigned NOT NULL,
    `month_year` varchar(7) NOT NULL,
    `period_start` date NOT NULL,
    `period_end` date NOT NULL,
    `days_worked` int DEFAULT '0',
    `total_hours` decimal(8,2) DEFAULT '0.00',
    `hourly_rate` decimal(8,2) DEFAULT '0.00',
    `overtime_hours` decimal(8,2) DEFAULT '0.00',
    `overtime_pay` decimal(10,2) DEFAULT '0.00',
    `gross_pay` decimal(10,2) NOT NULL,
    `epf_employee` decimal(10,2) DEFAULT '0.00',
    `epf_employer` decimal(10,2) DEFAULT '0.00',
    `epf_rate_employee` decimal(5,2) DEFAULT '11.00',
    `epf_rate_employer` decimal(5,2) DEFAULT '13.00',
    `socso_employee` decimal(10,2) DEFAULT '0.00',
    `socso_employer` decimal(10,2) DEFAULT '0.00',
    `eis_employee` decimal(10,2) DEFAULT '0.00',
    `eis_employer` decimal(10,2) DEFAULT '0.00',
    `pcb` decimal(10,2) DEFAULT '0.00',
    `total_statutory` decimal(10,2) DEFAULT '0.00',
    `employer_contribution` decimal(10,2) DEFAULT '0.00',
    `deductions` decimal(10,2) DEFAULT '0.00',
    `deduction_notes` text,
    `allowances` decimal(10,2) DEFAULT '0.00',
    `allowance_notes` text,
    `net_pay` decimal(10,2) NOT NULL,
    `status` enum('draft','approved','paid') DEFAULT 'draft',
    `generated_by` bigint unsigned DEFAULT NULL,
    `paid_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `payrolls_user_month_unique` (`user_id`,`month_year`),
    KEY `payrolls_user_id_index` (`user_id`),
    KEY `payrolls_generated_by_index` (`generated_by`),
    CONSTRAINT `payrolls_user_id_foreign` 
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `payrolls_generated_by_foreign` 
        FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Key Columns

| Column | Type | Description |
|--------|------|-------------|
| month_year | varchar(7) | Format: YYYY-MM |
| days_worked | int | Working days in period |
| total_hours | decimal | Total hours worked |
| overtime_hours | decimal | Hours over 8/day |
| gross_pay | decimal | Total before deductions |
| epf_employee | decimal | EPF (employee) |
| epf_employer | decimal | EPF (employer) |
| socso_employee | decimal | SOCSO (employee) |
| socso_employer | decimal | SOCSO (employer) |
| eis_employee | decimal | EIS (employee) |
| eis_employer | decimal | EIS (employer) |
| total_statutory | decimal | Sum of employee deductions |
| net_pay | decimal | Take-home amount |

### Status Values
| Status | Description |
|--------|-------------|
| draft | Pending review |
| approved | Approved by admin |
| paid | Payment completed |

---

## Table: settings

**Purpose:** Store system configuration

```sql
CREATE TABLE `settings` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `key` varchar(255) NOT NULL,
    `value` text,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Default Settings

| Key | Default Value | Description |
|-----|---------------|-------------|
| company_name | ClockWise Sdn Bhd | Company name |
| company_address | Kuala Lumpur | Company address |
| company_email | info@clockwise.my | Company email |
| company_phone | +60-3-1234-5678 | Company phone |
| office_ip | 192.168.1.1 | Office network IP |
| work_start_time | 09:00 | Work start time |
| work_end_time | 18:00 | Work end time |
| late_threshold_minutes | 15 | Grace period |
| overtime_rate_multiplier | 1.5 | OT rate multiplier |
| epf_employee_rate | 11 | EPF employee % |
| epf_employer_rate | 13 | EPF employer % |

---

## Table: audit_logs

**Purpose:** Track security and activity events

```sql
CREATE TABLE `audit_logs` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint unsigned DEFAULT NULL,
    `action` varchar(255) NOT NULL,
    `model_type` varchar(255) DEFAULT NULL,
    `model_id` bigint unsigned DEFAULT NULL,
    `old_values` json DEFAULT NULL,
    `new_values` json DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text,
    `description` text,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `audit_logs_user_id_index` (`user_id`),
    KEY `audit_logs_action_index` (`action`),
    CONSTRAINT `audit_logs_user_id_foreign` 
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Action Types

| Category | Action | Description |
|----------|--------|-------------|
| Auth | auth.login_success | Successful login |
| Auth | auth.login_failed | Failed login attempt |
| Auth | auth.logout | User logout |
| Employee | employee.created | New employee created |
| Employee | employee.updated | Employee details updated |
| Employee | employee.deleted | Employee deleted |
| Leave | leave.created | Leave request submitted |
| Leave | leave.approved | Leave approved |
| Leave | leave.rejected | Leave rejected |
| Payroll | payroll.created | Payroll generated |
| Payroll | payroll.approved | Payroll approved |
| Payroll | payroll.paid | Payroll marked as paid |
| Settings | settings.updated | System settings changed |

---

## Table: password_reset_tokens

**Purpose:** Temporary password reset tokens

```sql
CREATE TABLE `password_reset_tokens` (
    `email` varchar(255) NOT NULL,
    `token` varchar(255) NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Table: sessions

**Purpose:** Laravel session storage

```sql
CREATE TABLE `sessions` (
    `id` varchar(255) NOT NULL,
    `user_id` bigint unsigned DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text,
    `payload` longtext NOT NULL,
    `last_activity` int NOT NULL,
    PRIMARY KEY (`id`),
    KEY `sessions_user_id_index` (`user_id`),
    KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Relationships Diagram

```
                         ┌─────────────────┐
                         │      USER       │
                         └─────────────────┘
                                 │
         ┌───────────────────────┼───────────────────────┐
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   ATTENDANCE    │    │  LEAVE_REQUEST  │    │     PAYROLL     │
│  belongs_to     │    │  belongs_to     │    │  belongs_to     │
│  user           │    │  user           │    │  user           │
└─────────────────┘    │  approved_by    │    │  generated_by   │
                       └─────────────────┘    └─────────────────┘
                                │
                                ▼
                       ┌─────────────────┐
                       │   AUDIT_LOG     │
                       │  belongs_to     │
                       │  user           │
                       └─────────────────┘
```

---

## Eloquent Relationships

### User Model

```php
class User extends Authenticatable
{
    // One-to-Many
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }
    
    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }
    
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
    
    // Approved leaves
    public function approvedLeaves()
    {
        return $this->hasMany(LeaveRequest::class, 'approved_by');
    }
}
```

### Attendance Model

```php
class Attendance extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### LeaveRequest Model

```php
class LeaveRequest extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
```

### Payroll Model

```php
class Payroll extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
```

---

## Queries Reference

### Common Queries

```php
// Get all employees
User::where('role', 'employee')->get();

// Get today's attendance
Attendance::whereDate('date', today())->get();

// Get pending leave requests
LeaveRequest::where('status', 'pending')->get();

// Get this month's payroll
Payroll::where('month_year', now()->format('Y-m'))->get();

// Get user's leave balance
$used = LeaveRequest::where('user_id', $userId)
    ->where('type', 'annual')
    ->where('status', 'approved')
    ->sum('days');
$balance = $user->annual_leave_entitlement - $used;
```

### Reporting Queries

```php
// Monthly attendance summary
Attendance::whereMonth('date', $month)
    ->whereYear('date', $year)
    ->selectRaw('
        user_id,
        COUNT(*) as total_days,
        SUM(TIMESTAMPDIFF(HOUR, clock_in, clock_out)) as total_hours,
        SUM(status = "late") as late_count
    ')
    ->groupBy('user_id')
    ->get();

// Payroll totals
Payroll::where('month_year', $monthYear)
    ->selectRaw('
        SUM(gross_pay) as total_gross,
        SUM(net_pay) as total_net,
        SUM(epf_employee) as total_epf_employee,
        SUM(epf_employer) as total_epf_employer
    ')
    ->first();
```

---

## Migration Order

Run migrations in this order:

1. `create_users_table` - Base user table
2. `create_cache_table` - Cache storage
3. `create_jobs_table` - Queue jobs
4. `add_details_to_user_table` - Additional user fields
5. `create_settings_table` - System settings
6. `create_payrolls_table` - Payroll records
7. `create_attendances_table` - Attendance records

---

## Backup Recommendations

### Critical Tables
- `users` - User accounts
- `payrolls` - Payroll records (legal requirement)
- `attendances` - Attendance history
- `leave_requests` - Leave records

### Backup Schedule
- Daily: Full database backup
- Weekly: Off-site backup
- Monthly: Archive and compress

### Backup Command
```bash
# MySQL dump
mysqldump -u clockwise -p clockwise > backup_$(date +%Y%m%d).sql

# Laravel backup (with spatie/laravel-backup)
php artisan backup:run
```

---

<p align="center">
  <em>ClockWise Database Schema v1.0</em><br>
  <em>Last Updated: January 2026</em>
</p>
