# ClockWise

**ClockWise** is a lightweight HRMS (Human Resource Management System) and attendance tracking application designed for small-to-medium enterprises (SMEs) in Malaysia. Built with simplicity in mind, it targets businesses with approximately 1 admin and ~20 employees, and is optimized to run on shared hosting environments (cPanel) without requiring SSH access or Node.js.

![Laravel](https://img.shields.io/badge/Laravel-11-red?style=flat-square&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2-blue?style=flat-square&logo=php)
![TailwindCSS](https://img.shields.io/badge/Tailwind-CDN-38bdf8?style=flat-square&logo=tailwindcss)

---

## ✨ Features

### Employee Features
- **Clock In/Out** - Daily attendance with automatic late detection (based on 9:00 AM threshold)
- **Location Detection** - Automatically detects if working from office or remote based on IP address
- **Attendance History** - View personal attendance records with filters
- **Leave Requests** - Submit MC, annual leave, emergency leave, or unpaid leave with file attachments
- **Payslips** - View and print personal payslips

### Admin Features
- **Dashboard** - Overview with employee count, today's attendance, pending requests, and monthly stats
- **Employee Management** - Full CRUD for managing employees (add, edit, delete, set hourly rates)
- **Attendance Log** - View all employee attendance records with filters
- **Leave Management** - Approve or reject leave requests with remarks
- **Payroll System** - Generate payroll based on attendance, calculate overtime (1.5x rate), add allowances/deductions
- **Reports & Analytics** - Visual charts showing attendance trends, department distribution, and monthly statistics
- **Office IP Configuration** - Set the office IP address for location-based attendance tracking

---

## 🛠️ Tech Stack

| Technology | Purpose |
|------------|---------|
| **Laravel 11** | PHP Framework |
| **PHP 8.2** | Server-side language |
| **MySQL** | Database |
| **Blade** | Templating engine |
| **Tailwind CSS (CDN)** | Styling (no build step required) |
| **Chart.js (CDN)** | Analytics charts |
| **Docker** | Local development environment |

---

## 📁 Project Structure

```
clockWise/
├── app/
│   ├── Http/Controllers/
│   │   ├── AdminController.php      # Admin dashboard, employees, attendance, leave, reports
│   │   ├── AttendanceController.php # Clock in/out, attendance history
│   │   ├── AuthController.php       # Login, register, logout
│   │   ├── LeaveController.php      # Leave requests CRUD
│   │   └── PayrollController.php    # Payroll generation and management
│   ├── Middleware/
│   │   └── CheckNetworkContext.php  # Office/remote IP detection
│   └── Models/
│       ├── Attendance.php
│       ├── Leave.php
│       ├── Payroll.php
│       ├── Setting.php
│       └── User.php
├── database/migrations/             # Database schema
├── resources/views/
│   ├── admin/                       # Admin panel views
│   ├── attendance/                  # Attendance history views
│   ├── auth/                        # Login/register views
│   ├── employee/                    # Employee dashboard
│   ├── layouts/                     # Shared layouts (app, guest)
│   ├── leave/                       # Leave request views
│   └── payroll/                     # Payslip views
└── routes/web.php                   # All application routes
```

---

## 🚀 Quick Setup

### Option 1: Docker (Recommended for Development)

1. Clone the repository:
   ```bash
   git clone https://github.com/Syafiq276/ClockWise.git
   cd ClockWise
   ```

2. Copy environment file:
   ```bash
   cp .env.example .env
   ```

3. Configure `.env` for Docker:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=db
   DB_PORT=3306
   DB_DATABASE=clockwise
   DB_USERNAME=clockwise
   DB_PASSWORD=clockwise
   ```

4. Start Docker containers:
   ```bash
   docker-compose up -d
   ```

5. Run migrations:
   ```bash
   docker-compose exec app php artisan migrate
   ```

6. Access the application at `http://localhost:8000`

### Option 2: Traditional Setup (XAMPP/Laragon)

1. Clone the repository to your web server directory

2. Install dependencies:
   ```bash
   composer install
   ```

3. Copy and configure environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Configure your database in `.env`

5. Run migrations:
   ```bash
   php artisan migrate
   ```

6. Start the server:
   ```bash
   php artisan serve
   ```

### Option 3: Shared Hosting (cPanel)

1. Upload all files to `public_html` or a subdomain folder
2. Point the domain to the `public` folder
3. Create a MySQL database and user via cPanel
4. Update `.env` with database credentials
5. Run migrations via cPanel's Terminal or import the SQL manually

---

## 👤 User Roles

| Role | Access |
|------|--------|
| **Admin** | Full access - manage employees, view all attendance, approve leave, generate payroll, view reports |
| **Employee** | Clock in/out, view own attendance, submit leave requests, view own payslips |

---

## 📊 Payroll Calculation

The payroll system automatically calculates:

- **Regular Hours**: Total hours worked (up to 8 hours/day)
- **Overtime Hours**: Hours exceeding 8 hours/day
- **Basic Pay**: Regular hours × Hourly rate
- **Overtime Pay**: Overtime hours × Hourly rate × 1.5
- **Gross Pay**: Basic pay + Overtime pay
- **Net Pay**: Gross pay + Allowances - Deductions

Payroll workflow: **Draft** → **Approved** → **Paid**

---

## 🔐 Authentication

- Standard Laravel authentication (login, register, logout)
- Role-based access control (admin vs employee)
- Middleware protection for admin routes

---

## 📝 Leave Types

- Annual Leave
- Medical Certificate (MC) - supports file upload
- Emergency Leave
- Unpaid Leave

Leave status workflow: **Pending** → **Approved/Rejected**

---

## 🌐 Network Context Detection

The system detects whether an employee is working from the office or remotely by comparing the request IP address with the configured office IP. This is displayed on the attendance record.

---

## 📈 Reports & Analytics

The admin dashboard includes visual charts powered by Chart.js:

- Attendance overview (present, late, absent)
- Monthly attendance trends
- Department distribution
- Leave type breakdown
- Payroll summary

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## 🙏 Acknowledgements

- [Laravel](https://laravel.com)
- [Tailwind CSS](https://tailwindcss.com)
- [Chart.js](https://www.chartjs.org)

---

Built with ❤️ for Malaysian SMEs
