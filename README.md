# 🕐 ClockWise - HRMS for Malaysian SME

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-red?style=flat-square&logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue?style=flat-square&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-8.0-orange?style=flat-square&logo=mysql" alt="MySQL">
  <img src="https://img.shields.io/badge/TailwindCSS-3.x-06B6D4?style=flat-square&logo=tailwindcss" alt="Tailwind">
  <img src="https://img.shields.io/badge/License-MIT-green?style=flat-square" alt="License">
</p>

<p align="center">
  A modern, lightweight Human Resource Management System designed specifically for Malaysian Small and Medium Enterprises. Built with Laravel 12 and featuring full compliance with Malaysian employment regulations including EPF, SOCSO, and EIS statutory deductions.
</p>

---

## 📋 Table of Contents

- [Features](#-features)
- [Quick Start](#-quick-start)
- [Default Credentials](#-default-credentials)
- [Project Structure](#-project-structure)
- [Malaysian Compliance](#-malaysian-compliance)
- [Configuration](#-configuration)
- [Deployment](#-deployment)
- [API Reference](#-api-reference)
- [Database Schema](#-database-schema)
- [License](#-license)

---

## ✨ Features

### 👤 Employee Management
- Employee registration and profile management
- Role-based access control (Admin/Employee)
- Employment details tracking (start date, position, hourly rate)
- Leave entitlement management (Annual: 12 days, MC: 14 days per Malaysian law)

### ⏰ Attendance Tracking
- One-click clock in/out with real-time status
- Location-based tracking (Office/Remote via IP detection)
- Late arrival detection (configurable threshold)
- Monthly attendance history with detailed statistics
- Overtime tracking

### 📅 Leave Management
- Multiple leave types:
  - Annual Leave (12 days default)
  - Medical Leave / MC (14 days default)
  - Emergency Leave
  - Unpaid Leave
- Leave balance tracking per Malaysian Employment Act
- Leave request submission with file attachments
- Admin approval/rejection workflow with remarks
- Automatic balance deduction upon approval
- Prevent over-requesting (balance validation)

### 💰 Payroll System
- **Malaysian Statutory Deductions:**
  - EPF/KWSP (Employee: 11%, Employer: 13%)
  - SOCSO/PERKESO (with contribution caps)
  - EIS (Employment Insurance System: 0.2%)
- Hourly rate-based calculation
- Overtime pay (1.5x multiplier)
- Allowances and additional deductions
- PDF payslip generation and download
- Monthly payroll batch processing
- Payroll status workflow (Draft → Approved → Paid)

### 📊 Reports & Analytics
- Attendance summary reports
- Payroll reports with totals
- Visual charts (attendance trends, department distribution)
- Employee statistics dashboard
- Export capabilities

### 🔐 Security Features
- Comprehensive audit logging (login, CRUD, approvals)
- Session management
- Password reset via email
- Role-based permissions
- CSRF protection

### 🎨 Modern UI/UX
- Floating sidebar navigation (expands on hover)
- Fully responsive design (mobile, tablet, desktop)
- Toast notifications for feedback
- Loading spinners and animations
- Smooth transitions and hover effects

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.2 or higher
- Composer 2.x
- MySQL 8.0
- Node.js & NPM (optional)

### Option 1: Standard Installation

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

### Option 3: cPanel Deployment (No Terminal)

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
│   │   └── MalaysianStatutory.php    # EPF, SOCSO, EIS calculations
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── EmployeeController.php
│   │   │   │   ├── AttendanceController.php
│   │   │   │   ├── LeaveController.php
│   │   │   │   ├── PayrollController.php
│   │   │   │   ├── ReportController.php
│   │   │   │   └── AuditLogController.php
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php
│   │   │   │   ├── ForgotPasswordController.php
│   │   │   │   └── ResetPasswordController.php
│   │   │   ├── AttendanceController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── LeaveController.php
│   │   │   └── PayrollController.php
│   │   └── Middleware/
│   │       └── CheckNetworkContext.php
│   └── Models/
│       ├── User.php
│       ├── Attendance.php
│       ├── LeaveRequest.php
│       ├── Payroll.php
│       ├── Setting.php
│       └── AuditLog.php
├── database/
│   ├── migrations/
│   │   ├── create_users_table.php
│   │   ├── create_attendances_table.php
│   │   ├── create_leave_requests_table.php
│   │   ├── create_payrolls_table.php
│   │   ├── create_settings_table.php
│   │   └── create_audit_logs_table.php
│   └── seeders/
│       └── DatabaseSeeder.php
├── resources/views/
│   ├── admin/                        # Admin panel views
│   ├── attendance/                   # Attendance views
│   ├── auth/                         # Authentication views
│   ├── dashboard/                    # Employee dashboard
│   ├── layouts/                      # Layout templates
│   ├── leave/                        # Leave management
│   └── payroll/                      # Payroll & payslips
├── routes/
│   └── web.php                       # All application routes
├── public/
│   ├── index.php
│   └── install.php                   # Web installer
├── docker-compose.yml
├── CPANEL_DEPLOYMENT.md
└── README.md
```

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

### Environment Variables (.env)

```env
# Application
APP_NAME=ClockWise
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_TIMEZONE=Asia/Kuala_Lumpur
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=clockwise
DB_USERNAME=root
DB_PASSWORD=secret

# Mail (for password reset)
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=465
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="ClockWise"

# Session & Cache
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

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
- [ ] Set folder permissions (storage: 775)
- [ ] Configure HTTPS/SSL

### Docker Deployment

```bash
# Production build
docker-compose -f docker-compose.prod.yml up -d

# Scale if needed
docker-compose up -d --scale app=3
```

### cPanel/Shared Hosting

1. Upload files via FileZilla
2. Create MySQL database
3. Configure `.env` with credentials
4. Access `install.php?token=clockwise2026`
5. Delete `install.php` after setup

See [CPANEL_DEPLOYMENT.md](CPANEL_DEPLOYMENT.md) for detailed steps.

---

## 🔌 API Reference

### Authentication Routes

| Method | URI | Description |
|--------|-----|-------------|
| GET | /login | Login page |
| POST | /login | Authenticate user |
| POST | /logout | Logout user |
| GET | /forgot-password | Password reset form |
| POST | /forgot-password | Send reset email |
| GET | /reset-password/{token} | Reset form |
| POST | /reset-password | Update password |

### Employee Routes

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
| GET | /payslips/{id}/download | Download PDF |

### Admin Routes

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
- created_at, updated_at
```

### Attendances Table
```sql
- id (PK)
- user_id (FK)
- date
- clock_in
- clock_out
- status (ontime/late)
- location_type (office/remote)
- ip_address
- created_at, updated_at
```

### Leave Requests Table
```sql
- id (PK)
- user_id (FK)
- type (annual/mc/emergency/unpaid)
- start_date
- end_date
- days
- reason
- attachment
- status (pending/approved/rejected)
- approved_by (FK)
- admin_remarks
- responded_at
- created_at, updated_at
```

### Payrolls Table
```sql
- id (PK)
- user_id (FK)
- month_year
- period_start, period_end
- days_worked
- total_hours
- hourly_rate
- overtime_hours
- overtime_pay
- gross_pay
- epf_employee, epf_employer
- socso_employee, socso_employer
- eis_employee, eis_employer
- total_statutory
- deductions, allowances
- net_pay
- status (draft/approved/paid)
- generated_by (FK)
- paid_at
- created_at, updated_at
```

### Audit Logs Table
```sql
- id (PK)
- user_id (FK)
- action
- model_type
- model_id
- old_values (JSON)
- new_values (JSON)
- ip_address
- user_agent
- description
- created_at, updated_at
```

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=AttendanceTest

# With coverage report
php artisan test --coverage
```

---

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing`)
5. Open a Pull Request

---

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

---

## 👨‍💻 Author

**Syafiq** - [GitHub](https://github.com/Syafiq276)

---

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP Framework for Web Artisans
- [Tailwind CSS](https://tailwindcss.com) - Utility-first CSS Framework
- [DomPDF](https://github.com/barryvdh/laravel-dompdf) - PDF Generation
- [Chart.js](https://www.chartjs.org/) - JavaScript Charts
- Malaysian Government - EPF, SOCSO, EIS official rates

---

<p align="center">
  <strong>Made with ❤️ for Malaysian SMEs</strong>
  <br>
  <sub>Simplifying HR management, one clock-in at a time.</sub>
</p>
