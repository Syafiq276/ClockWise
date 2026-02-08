# 🕐 ClockWise - HRMS for Malaysian SME

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-red?style=flat-square&logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue?style=flat-square&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-8.0-orange?style=flat-square&logo=mysql" alt="MySQL">
  <img src="https://img.shields.io/badge/TailwindCSS-3.x-06B6D4?style=flat-square&logo=tailwindcss" alt="Tailwind">
  <img src="https://img.shields.io/badge/Sanctum-API-purple?style=flat-square&logo=laravel" alt="Sanctum">
  <img src="https://img.shields.io/badge/CI-GitHub%20Actions-2088FF?style=flat-square&logo=github-actions" alt="CI">
  <img src="https://img.shields.io/badge/License-MIT-green?style=flat-square" alt="License">
</p>

<p align="center">
  A modern, lightweight Human Resource Management System designed specifically for Malaysian Small and Medium Enterprises. Built with Laravel 12 and featuring full compliance with Malaysian employment regulations including EPF, SOCSO, and EIS statutory deductions. Includes a REST API for mobile/third-party integration and a built-in Digital Asset Management system.
</p>

---

## 📋 Table of Contents

- [Features](#-features)
- [Quick Start](#-quick-start)
- [Default Credentials](#-default-credentials)
- [Project Structure](#-project-structure)
- [REST API](#-rest-api)
- [Malaysian Compliance](#-malaysian-compliance)
- [Configuration](#-configuration)
- [Testing & CI/CD](#-testing--cicd)
- [Deployment](#-deployment)
- [Database Schema](#-database-schema)
- [License](#-license)

---

## ✨ Features

### 👤 Employee Management
- Employee registration and profile management
- Role-based access control (Admin / Employee)
- Employment details tracking (start date, position, hourly rate)
- Leave entitlement management (Annual: 12 days, MC: 14 days per Malaysian law)

### ⏰ Attendance Tracking
- One-click clock in/out with real-time status
- Location-based tracking (Office / Remote via IP detection)
- Late arrival detection (configurable threshold)
- Monthly attendance history with detailed statistics
- Overtime hours tracking
- Total hours and overtime metrics per record

### 📅 Leave Management
- Multiple leave types:
  - Annual Leave (12 days default)
  - Medical Leave / MC (14 days default)
  - Emergency Leave
  - Unpaid Leave
- Leave balance tracking per Malaysian Employment Act
- Leave request submission with file attachments (PDF, JPG, PNG)
- Admin approval/rejection workflow with remarks
- Automatic balance deduction upon approval
- Prevent over-requesting (balance validation)

### 💰 Payroll System
- **Malaysian Statutory Deductions:**
  - EPF/KWSP (Employee: 11%, Employer: 13%)
  - SOCSO/PERKESO (with contribution caps)
  - EIS (Employment Insurance System: 0.2%)
  - PCB/MTD (Monthly Tax Deduction)
- Hourly rate-based calculation
- Overtime pay (1.5x multiplier)
- Allowances and additional deductions with notes
- PDF payslip generation and download (via DomPDF)
- Monthly payroll batch processing
- Payroll status workflow (Draft → Approved → Paid)

### 📊 Reports & Analytics
- Attendance summary reports
- Payroll reports with totals
- Visual charts (attendance trends, department distribution)
- Employee statistics dashboard
- Export capabilities

### 📁 Digital Asset Management (DAM)
- Upload and organise company files and images
- Folder-based organisation
- Grid and list view toggle
- EXIF-based photo date detection (4-strategy: EXIF → GPS → XMP → file modified)
- Role-based access (Admin: full access, Employee: images only + download)
- Image preview streaming through controller

### 🔐 Security Features
- Comprehensive audit logging (login, CRUD, approvals)
- Security headers middleware
- Admin role middleware (`IsAdmin`)
- Network context detection (office/remote)
- Password reset via email
- CSRF protection
- Laravel Sanctum API token authentication

### 🎨 Modern UI/UX
- Floating sidebar navigation (expands on hover)
- Fully responsive design (mobile, tablet, desktop)
- Toast notifications for feedback
- Loading spinners with smooth CSS transitions
- Animated login/register cards
- Consistent auth page design

### 🔌 REST API (Sanctum)
- 19 API endpoints for mobile/third-party integration
- Bearer token authentication
- Endpoints for auth, attendance, leave, payroll, and profile
- JSON responses with pagination support
- See [REST API section](#-rest-api) for full details

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.2 or higher
- Composer 2.x
- MySQL 8.0
- Node.js & NPM (for Vite asset compilation, optional in production)

### Option 1: Standard Installation (XAMPP / Local)

```bash
# Clone the repository
git clone https://github.com/Syafiq276/ClockWise.git
cd ClockWise

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
# DB_DATABASE=clockwise
# DB_USERNAME=root
# DB_PASSWORD=

# Run migrations with sample data
php artisan migrate --seed

# Create storage symlink
php artisan storage:link

# Start development server
php artisan serve
```

Access at: http://localhost:8000

### Option 2: Docker Installation

```bash
# Clone the repository
git clone https://github.com/Syafiq276/ClockWise.git
cd ClockWise

# Start all containers
docker-compose up -d

# Run migrations and seeding
docker-compose exec app php artisan migrate --seed

# Access at http://localhost:8000
```

### Option 3: Render Deployment

See [RENDER_DEPLOYMENT.md](RENDER_DEPLOYMENT.md) for deploying to Render.com with automatic deploys on push.

### Option 4: cPanel Deployment (No Terminal)

See [CPANEL_DEPLOYMENT.md](CPANEL_DEPLOYMENT.md) for detailed FileZilla upload instructions with web-based installer.

---

## 🔑 Default Credentials

| Role | Email | Password | Access |
|------|-------|----------|--------|
| Admin | admin@clockwise.my | password123 | Full system access |
| Employee | ali@clockwise.my | password123 | Personal dashboard only |

**Sample Employees (after seeding):**
- Siti Nurhaliza (siti@clockwise.my)
- Kumar a/l Rajan (kumar@clockwise.my)
- Tan Mei Ling (mei@clockwise.my)
- Farid Kamil (farid@clockwise.my)

---

## 📁 Project Structure

```
clockWise/
├── app/
│   ├── Helpers/
│   │   └── MalaysianStatutory.php       # EPF, SOCSO, EIS calculations
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminController.php       # Admin dashboard, employees, payroll, leave, reports, audit
│   │   │   ├── Api/
│   │   │   │   ├── AuthController.php    # API login, register, logout, me
│   │   │   │   ├── AttendanceController.php  # API clock in/out, today, history, stats
│   │   │   │   ├── LeaveController.php   # API leave CRUD, balances
│   │   │   │   ├── PayrollController.php # API payslip list, show
│   │   │   │   └── ProfileController.php # API profile show, update, password
│   │   │   ├── AssetController.php       # DAM file management
│   │   │   ├── AttendanceController.php  # Web clock in/out
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php
│   │   │   │   ├── RegisterController.php
│   │   │   │   ├── ForgotPasswordController.php
│   │   │   │   └── ResetPasswordController.php
│   │   │   ├── FolderController.php      # DAM folder management
│   │   │   ├── LeaveController.php       # Web leave requests
│   │   │   └── PayrollController.php     # Web payslips
│   │   └── Middleware/
│   │       ├── CheckNetworkContext.php    # Office/remote IP detection
│   │       ├── IsAdmin.php               # Admin role guard
│   │       └── SecurityHeaders.php       # HTTP security headers
│   ├── Models/
│   │   ├── Asset.php                     # DAM assets
│   │   ├── Attendance.php
│   │   ├── AuditLog.php
│   │   ├── Folder.php                    # DAM folders
│   │   ├── LeaveRequest.php
│   │   ├── Payroll.php
│   │   ├── Setting.php
│   │   └── User.php
│   └── Services/
│       └── AssetUploadService.php        # EXIF date extraction & file processing
├── database/
│   ├── factories/
│   │   └── UserFactory.php
│   ├── migrations/                       # 18 migration files
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── ForcePasswordResetSeeder.php
├── resources/views/
│   ├── admin/                            # Admin panel views
│   │   ├── attendance/
│   │   ├── employees/                    # CRUD views
│   │   ├── leave/
│   │   ├── payroll/
│   │   ├── dashboard.blade.php
│   │   ├── reports.blade.php
│   │   └── audit-logs.blade.php
│   ├── assets/                           # DAM views
│   ├── attendance/                       # Clock in/out views
│   ├── auth/                             # Login, register, forgot/reset password
│   ├── components/                       # Reusable Blade components
│   ├── layouts/
│   │   ├── app.blade.php                 # Authenticated layout with sidebar
│   │   └── guest.blade.php              # Guest layout (auth pages)
│   ├── leave/                            # Leave management views
│   ├── payroll/                          # Payslip views
│   └── dashboard.blade.php              # Employee dashboard
├── routes/
│   ├── web.php                           # Web routes (auth + admin)
│   └── api.php                           # REST API routes (19 endpoints)
├── tests/
│   ├── Feature/
│   │   ├── ApiTest.php                   # 26 API endpoint tests
│   │   └── ExampleTest.php
│   └── Unit/
│       └── ExampleTest.php
├── .github/workflows/
│   ├── tests.yml                         # CI: PHPUnit on push/PR
│   └── fly-deploy.yml                    # CD: Fly.io deployment
├── docker-compose.yml
├── fly.toml
├── CPANEL_DEPLOYMENT.md
├── RENDER_DEPLOYMENT.md
└── README.md
```

---

## 🔌 REST API

ClockWise includes a full REST API powered by **Laravel Sanctum** for mobile apps and third-party integrations.

**Base URL:** `/api`
**Authentication:** Bearer token (obtained via `/api/login`)

### Public Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/login` | Authenticate and receive bearer token |
| POST | `/api/register` | Create new employee account |

### Protected Endpoints (Bearer Token Required)

#### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/logout` | Revoke current token |
| GET | `/api/me` | Get authenticated user details |

#### Profile
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/profile` | View full profile with leave entitlements |
| PUT | `/api/profile` | Update name/email |
| PUT | `/api/profile/password` | Change password |

#### Attendance
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/attendance/today` | Today's status + clock state |
| POST | `/api/attendance/clock-in` | Clock in (auto-detects office/remote) |
| POST | `/api/attendance/clock-out` | Clock out |
| GET | `/api/attendance/history` | Paginated history (?month=&per_page=) |
| GET | `/api/attendance/stats` | Monthly stats (total, on-time, late) |

#### Leave
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/leave/balances` | Annual & MC balances for current year |
| GET | `/api/leave` | List leave requests (?status=&type=) |
| POST | `/api/leave` | Submit new leave request |
| GET | `/api/leave/{id}` | View leave request detail |
| DELETE | `/api/leave/{id}` | Cancel pending leave request |

#### Payslips
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/payslips` | List payslips (?status=&year=) |
| GET | `/api/payslips/{id}` | View full payslip detail |

### Web Routes

#### Authentication
| Method | URI | Description |
|--------|-----|-------------|
| GET | /login | Login page |
| POST | /login | Authenticate user |
| GET | /register | Registration page |
| POST | /register | Create account |
| POST | /logout | Logout user |
| GET | /forgot-password | Password reset form |
| POST | /forgot-password | Send reset email |
| GET | /reset-password/{token} | Reset form |
| POST | /reset-password | Update password |

#### Employee
| Method | URI | Description |
|--------|-----|-------------|
| GET | /dashboard | Employee dashboard |
| POST | /attendance/clock-in | Clock in |
| POST | /attendance/clock-out | Clock out |
| GET | /attendance/history | Attendance history |
| GET | /leave | Leave requests list |
| GET | /leave/create | New leave form |
| POST | /leave | Submit leave request |
| GET | /payslips | My payslips |
| GET | /payslips/{id}/download | Download PDF payslip |
| GET | /assets | Digital asset manager |

#### Admin
| Method | URI | Description |
|--------|-----|-------------|
| GET | /admin | Admin dashboard |
| GET | /admin/employees | Employee list |
| POST | /admin/employees | Create employee |
| PUT | /admin/employees/{id} | Update employee |
| DELETE | /admin/employees/{id} | Delete employee |
| GET | /admin/attendance | Attendance log |
| GET | /admin/leave | Leave management |
| POST | /admin/leave/{id}/approve | Approve leave |
| POST | /admin/leave/{id}/reject | Reject leave |
| GET | /admin/payroll | Payroll list |
| POST | /admin/payroll/generate | Generate payroll |
| GET | /admin/reports | Reports & analytics |
| GET | /admin/audit-logs | Security audit logs |

---

## 🇲🇾 Malaysian Compliance

### EPF/KWSP Contribution Rates (2024)

| Employee Age | Employee Rate | Employer Rate |
|--------------|---------------|---------------|
| Below 60 years | 11% | 13% |
| 60 years and above | 0% (optional 5.5%) | 4% |

**Note:** Employees may choose to contribute at a lower rate (optional).

### SOCSO/PERKESO Contribution

Based on salary bands as per SOCSO Act 1969:

| Monthly Wage | Employee | Employer |
|--------------|----------|----------|
| ≤ RM30 | RM0.10 | RM0.40 |
| RM5,001 - RM6,000 | RM58.85 | RM206.15 |
| > RM6,000 (capped) | RM98.05 | RM343.15 |

**Coverage:**
- Employment Injury Scheme
- Invalidity Scheme

### EIS (Employment Insurance System)

| Component | Rate | Maximum |
|-----------|------|---------|
| Employee | 0.2% | RM39.90/month |
| Employer | 0.2% | RM39.90/month |

**Coverage:**
- Job Search Allowance
- Reduced Income Allowance
- Training Allowance

### Leave Entitlement (Employment Act 1955)

| Leave Type | Entitlement |
|------------|-------------|
| Annual Leave | 8-16 days (based on service) |
| Sick Leave (MC) | 14-22 days (based on service) |
| Maternity Leave | 98 days |
| Paternity Leave | 7 days |

**ClockWise Defaults:**
- Annual Leave: 12 days
- Medical Leave: 14 days

---

## ⚙️ Configuration

### System Settings (Admin Panel)

| Setting | Description | Default |
|---------|-------------|---------|
| company_name | Company name on payslips | TechCorp Sdn Bhd |
| work_start_time | Official work start | 09:00 |
| work_end_time | Official work end | 18:00 |
| late_threshold_minutes | Minutes before marked late | 15 |
| overtime_rate_multiplier | OT pay multiplier | 1.5 |
| kwsp_employee_rate | EPF employee % | 11 |
| kwsp_employer_rate | EPF employer % | 13 |
| office_ip | Office IP for location detection | — |

---

## 🧪 Testing & CI/CD

### Running Tests Locally

```bash
# Run all tests (uses SQLite :memory:)
php artisan test

# Run only API tests
php artisan test --filter=ApiTest

# Run a specific test
php artisan test --filter=test_user_can_login_with_valid_credentials

# With coverage report
php artisan test --coverage
```

### Test Suite

| Test File | Tests | Coverage |
|-----------|-------|----------|
| `tests/Feature/ApiTest.php` | 26 | Auth, Attendance, Leave, Payslips, Profile APIs |
| `tests/Feature/ExampleTest.php` | 1 | Application redirect |
| `tests/Unit/ExampleTest.php` | 1 | Basic unit test |

### CI/CD Pipeline

**GitHub Actions** runs automatically on every push and pull request to `main`:

- ✅ Sets up PHP 8.2 with required extensions
- ✅ Installs Composer dependencies
- ✅ Runs full PHPUnit test suite against SQLite in-memory database
- ✅ Workflow file: `.github/workflows/tests.yml`

**Continuous Deployment** is configured via Render (auto-deploy on push to `main`).

---

## 🚢 Deployment

### Production Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate new `APP_KEY`
- [ ] Configure database credentials
- [ ] Set up email for password reset
- [ ] Run `composer install --no-dev`
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Run `php artisan storage:link`
- [ ] Set folder permissions (storage: 775)
- [ ] Configure HTTPS/SSL

### Render (Recommended)

See [RENDER_DEPLOYMENT.md](RENDER_DEPLOYMENT.md) for step-by-step Render.com deployment with auto-deploy on push.

### Docker

```bash
docker-compose up -d
docker-compose exec app php artisan migrate --seed
```

### Fly.io

Fly.io deployment config is included in `fly.toml` and `.github/workflows/fly-deploy.yml`.

See [FLY_DEPLOYMENT.md](FLY_DEPLOYMENT.md) for details.

### cPanel / Shared Hosting

1. Upload files via FileZilla
2. Create MySQL database
3. Configure `.env` with credentials
4. Access `install.php?token=clockwise2026`
5. Delete `install.php` after setup

See [CPANEL_DEPLOYMENT.md](CPANEL_DEPLOYMENT.md) for detailed steps.

---

## 🗄️ Database Schema

### Users Table
```sql
- id (PK)
- name
- email (unique)
- password
- role (admin/employee)
- position
- hourly_rate
- annual_leave_entitlement
- mc_entitlement
- employment_start_date
- created_at, updated_at, deleted_at
```

### Attendances Table
```sql
- id (PK)
- user_id (FK → users)
- date
- clock_in, clock_out
- total_hours, overtime_hours
- status (ontime/late)
- location_type (office/remote)
- ip_address
- created_at, updated_at
```

### Leave Requests Table
```sql
- id (PK)
- user_id (FK → users)
- type (annual/mc/emergency/unpaid)
- start_date, end_date
- days
- reason
- attachment
- status (pending/approved/rejected)
- approved_by (FK → users)
- admin_remarks
- responded_at
- created_at, updated_at
```

### Payrolls Table
```sql
- id (PK)
- user_id (FK → users)
- month_year
- period_start, period_end
- days_worked
- total_hours, hourly_rate
- overtime_hours, overtime_pay
- gross_pay
- epf_employee, epf_employer, epf_rate_employee, epf_rate_employer
- socso_employee, socso_employer
- eis_employee, eis_employer
- pcb
- total_statutory, employer_contribution
- deductions, deduction_notes
- allowances, allowance_notes
- net_pay
- status (draft/approved/paid)
- generated_by (FK → users)
- paid_at
- created_at, updated_at
```

### Audit Logs Table
```sql
- id (PK)
- user_id (FK → users)
- action
- model_type, model_id
- old_values (JSON), new_values (JSON)
- ip_address, user_agent
- description
- created_at, updated_at
```

### Assets Table (DAM)
```sql
- id (PK)
- folder_id (FK → folders, nullable)
- uploaded_by (FK → users)
- filename, original_filename
- path, mime_type, size
- taken_at
- created_at, updated_at
```

### Folders Table (DAM)
```sql
- id (PK)
- name
- parent_id (FK → folders, nullable)
- created_by (FK → users)
- created_at, updated_at
```

---

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing`)
3. Write tests for new features
4. Ensure all tests pass (`php artisan test`)
5. Commit changes (`git commit -m 'Add amazing feature'`)
6. Push to branch (`git push origin feature/amazing`)
7. Open a Pull Request

---

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

---

## 👨‍💻 Author

**Syafiq** - [GitHub](https://github.com/Syafiq276)

---

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP Framework for Web Artisans
- [Laravel Sanctum](https://laravel.com/docs/sanctum) - API Token Authentication
- [Tailwind CSS](https://tailwindcss.com) - Utility-first CSS Framework
- [DomPDF](https://github.com/barryvdh/laravel-dompdf) - PDF Generation
- [Chart.js](https://www.chartjs.org/) - JavaScript Charts
- [PHPUnit](https://phpunit.de/) - Testing Framework
- [GitHub Actions](https://github.com/features/actions) - CI/CD Pipeline
- Malaysian Government - EPF, SOCSO, EIS official rates

---

<p align="center">
  <strong>Made with ❤️ for Malaysian SMEs</strong>
  <br>
  <sub>Simplifying HR management, one clock-in at a time.</sub>
</p>
