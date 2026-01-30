# ClockWise

ClockWise is a lightweight HRMS/attendance system for a small SME in Malaysia (1 admin, ~20 employees). It is built to run on shared hosting without SSH or Node.js, using Laravel 11, Blade views, and Tailwind via CDN.

## Key Features

- Daily clock-in/clock-out with late/ontime status
- Office vs. remote detection using a stored office IP
- Simple dashboard for employees
- Admin can set the office IP address

## Tech Stack

- Laravel 11 (PHP 8.2)
- MySQL
- Blade templates + Tailwind CSS (CDN)
- Laravel Breeze (Blade)

## Quick Setup (Local)

1. Copy env file and configure database:
	- `.env` (set `DB_*` values)
2. Install dependencies:
	- `composer install`
3. Run migrations:
	- `php artisan migrate`
4. Start server:
	- `php artisan serve`

## Notes

- Tailwind is loaded from CDN for compatibility with cPanel shared hosting.
- The system uses middleware to detect office vs. remote by comparing request IP with the stored office IP.
