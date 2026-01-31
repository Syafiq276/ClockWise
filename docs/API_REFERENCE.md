# 📡 ClockWise API Reference

> Complete reference for all web routes and their functionality

---

## Authentication Routes

### Login Page

```http
GET /login
```

**Description:** Display login form

**Response:** HTML login page

---

### Process Login

```http
POST /login
```

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| email | string | Yes | User email address |
| password | string | Yes | User password |
| remember | boolean | No | Remember session |

**Response:**
- Success: Redirect to `/dashboard` (employee) or `/admin` (admin)
- Failure: Redirect back with error message

**Example:**
```
email: john@company.com
password: secret123
remember: true
```

---

### Logout

```http
POST /logout
```

**Description:** End user session

**Response:** Redirect to `/login`

---

### Forgot Password

```http
GET /forgot-password
POST /forgot-password
```

**POST Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| email | string | Yes | Registered email |

**Response:** Email with reset link sent

---

### Reset Password

```http
GET /reset-password/{token}
POST /reset-password
```

**POST Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| token | string | Yes | Reset token |
| email | string | Yes | User email |
| password | string | Yes | New password (min 8 chars) |
| password_confirmation | string | Yes | Password confirmation |

---

## Employee Routes

> All routes require authentication

### Dashboard

```http
GET /dashboard
```

**Description:** Employee dashboard with today's attendance status

**Response Data:**
| Field | Type | Description |
|-------|------|-------------|
| user | User | Current user |
| todayAttendance | Attendance | Today's clock in/out |
| recentAttendance | Collection | Last 5 attendance records |
| pendingLeaves | int | Pending leave count |
| leaveBalance | array | Annual and MC balance |

---

### Clock In

```http
POST /attendance/clock-in
```

**Description:** Record employee clock in

**Auto-captured Data:**
| Field | Description |
|-------|-------------|
| ip_address | Client IP address |
| location_type | 'office' or 'remote' |
| status | 'ontime' or 'late' (after 9:15 AM) |

**Response:**
- Success: Redirect with success message
- Already clocked in: Redirect with error message

---

### Clock Out

```http
POST /attendance/clock-out
```

**Description:** Record employee clock out

**Validation:**
- Must have clocked in today
- Cannot clock out if already done

**Response:**
- Success: Redirect with success message
- Error: Redirect with error message

---

### Attendance History

```http
GET /attendance/history
```

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| month | string | current | Filter by month (YYYY-MM) |
| page | int | 1 | Pagination |

**Response Data:**
| Field | Type | Description |
|-------|------|-------------|
| attendances | Paginated | Attendance records |
| totalDays | int | Days worked |
| totalHours | float | Hours worked |
| lateCount | int | Late arrivals |

---

### Leave Management

```http
GET /leave
```

**Description:** View leave requests and balance

**Response Data:**
| Field | Type | Description |
|-------|------|-------------|
| leaves | Collection | User's leave requests |
| annualBalance | int | Remaining annual leave |
| mcBalance | int | Remaining MC days |
| annualEntitlement | int | Total annual entitlement |
| mcEntitlement | int | Total MC entitlement |

---

### Submit Leave Request

```http
POST /leave
```

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| type | string | Yes | annual, mc, emergency, unpaid |
| start_date | date | Yes | Leave start date |
| end_date | date | Yes | Leave end date |
| reason | string | Yes | Reason for leave |
| attachment | file | No | Supporting document (MC cert) |

**Validation:**
- Start date must be today or future
- End date must be >= start date
- Sufficient leave balance (for annual/MC)
- File max 5MB, PDF/JPG/PNG

**Response:**
- Success: Redirect with success message
- Validation error: Redirect with errors

---

### Cancel Leave Request

```http
POST /leave/{id}/cancel
```

**Description:** Cancel pending leave request

**Validation:**
- Must be own request
- Status must be 'pending'

---

### View Payslips

```http
GET /payslips
```

**Description:** View all payslips

**Response Data:**
| Field | Type | Description |
|-------|------|-------------|
| payslips | Collection | All user's payslips |

---

### Download Payslip PDF

```http
GET /payslips/{id}/download
```

**Description:** Download payslip as PDF

**Response:** PDF file download

**Authorization:** Must be own payslip

---

## Admin Routes

> All routes require admin role

### Admin Dashboard

```http
GET /admin
```

**Response Data:**
| Field | Type | Description |
|-------|------|-------------|
| totalEmployees | int | Employee count |
| presentToday | int | Clocked in today |
| pendingLeaves | int | Pending leave requests |
| pendingPayrolls | int | Draft payrolls |
| recentActivity | Collection | Recent audit logs |
| attendanceChart | array | Weekly attendance data |

---

## Employee Management

### List Employees

```http
GET /admin/employees
```

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| search | string | Search by name/email |
| status | string | Filter: active/inactive |
| page | int | Pagination |

**Response Data:**
| Field | Type | Description |
|-------|------|-------------|
| employees | Paginated | Employee list |

---

### Create Employee

```http
POST /admin/employees
```

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| name | string | Yes | Full name |
| email | string | Yes | Unique email |
| password | string | Yes | Min 8 characters |
| position | string | No | Job position |
| hourly_rate | decimal | Yes | Hourly rate (RM) |
| annual_leave_entitlement | int | No | Default: 12 |
| mc_entitlement | int | No | Default: 14 |
| employment_start_date | date | Yes | Start date |

**Response:**
- Success: Redirect with success message
- Validation error: Redirect with errors

---

### Update Employee

```http
PUT /admin/employees/{id}
```

**Request Body:** Same as create (password optional)

---

### Delete Employee

```http
DELETE /admin/employees/{id}
```

**Description:** Delete employee and related data

**Response:**
- Success: Redirect with success message
- Cannot delete admin: Error message

---

## Attendance Management

### View All Attendance

```http
GET /admin/attendance
```

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| date | date | Filter by date |
| employee_id | int | Filter by employee |
| status | string | ontime/late/absent |

---

### Manual Attendance Entry

```http
POST /admin/attendance
```

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| user_id | int | Yes | Employee ID |
| date | date | Yes | Attendance date |
| clock_in | time | Yes | Clock in time |
| clock_out | time | No | Clock out time |
| status | string | No | ontime/late |

---

### Edit Attendance

```http
PUT /admin/attendance/{id}
```

**Request Body:** Same as create

---

## Leave Management (Admin)

### View All Leave Requests

```http
GET /admin/leave
```

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| status | string | pending/approved/rejected |
| type | string | annual/mc/emergency/unpaid |
| employee_id | int | Filter by employee |

---

### Approve Leave

```http
POST /admin/leave/{id}/approve
```

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| admin_remarks | string | No | Admin comments |

**Side Effects:**
- Updates leave status to 'approved'
- Records approval timestamp
- Creates audit log

---

### Reject Leave

```http
POST /admin/leave/{id}/reject
```

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| admin_remarks | string | Yes | Rejection reason |

**Side Effects:**
- Updates leave status to 'rejected'
- Restores leave balance (if already deducted)
- Creates audit log

---

## Payroll Management

### View Payrolls

```http
GET /admin/payroll
```

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| month_year | string | Filter: YYYY-MM |
| status | string | draft/approved/paid |
| employee_id | int | Filter by employee |

---

### Generate Payroll

```http
POST /admin/payroll/generate
```

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| month_year | string | Yes | Format: YYYY-MM |
| employees | array | No | Specific employee IDs |

**Calculation Process:**
1. Get attendance for period
2. Calculate hours worked
3. Calculate overtime (>8 hrs/day)
4. Calculate gross pay
5. Calculate EPF (11% employee, 13% employer)
6. Calculate SOCSO (capped table)
7. Calculate EIS (0.2%, capped)
8. Calculate net pay

**Response:**
- Success: Redirect with generated count
- Error: Redirect with error message

---

### Approve Payroll

```http
POST /admin/payroll/{id}/approve
```

**Description:** Change status from draft to approved

---

### Mark Payroll Paid

```http
POST /admin/payroll/{id}/paid
```

**Description:** Mark payroll as paid

**Side Effects:**
- Updates status to 'paid'
- Records paid_at timestamp
- Creates audit log

---

### Download Payslip PDF (Admin)

```http
GET /admin/payroll/{id}/pdf
```

**Description:** Download any employee's payslip

---

## Reports

### Attendance Report

```http
GET /admin/reports/attendance
```

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| start_date | date | Report start |
| end_date | date | Report end |
| employee_id | int | Optional filter |

**Response Data:**
| Field | Type | Description |
|-------|------|-------------|
| data | array | Daily attendance summary |
| totals | array | Summary totals |

---

### Leave Report

```http
GET /admin/reports/leave
```

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| year | int | Report year |
| type | string | Leave type filter |

---

### Payroll Report

```http
GET /admin/reports/payroll
```

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| month_year | string | Report period |

**Response Data:**
| Field | Type | Description |
|-------|------|-------------|
| summary | array | Total gross, deductions, net |
| statutory | array | EPF, SOCSO, EIS totals |
| byEmployee | array | Breakdown per employee |

---

## System Settings

### View Settings

```http
GET /admin/settings
```

**Response Data:** All system settings

---

### Update Settings

```http
POST /admin/settings
```

**Request Body:**
| Field | Type | Description |
|-------|------|-------------|
| company_name | string | Company name |
| company_address | string | Company address |
| company_email | string | Company email |
| company_phone | string | Company phone |
| office_ip | string | Office network IP |
| work_start_time | time | Default: 09:00 |
| work_end_time | time | Default: 18:00 |
| late_threshold_minutes | int | Default: 15 |
| overtime_rate_multiplier | decimal | Default: 1.5 |
| epf_employee_rate | decimal | Default: 11 |
| epf_employer_rate | decimal | Default: 13 |

---

## Audit Logs

### View Audit Logs

```http
GET /admin/audit-logs
```

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| action | string | Filter by action type |
| user_id | int | Filter by user |
| date_from | date | Start date |
| date_to | date | End date |

**Response Data:**
| Field | Type | Description |
|-------|------|-------------|
| logs | Paginated | Audit log entries |

---

## Response Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 302 | Redirect |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Server Error |

---

## Common Response Formats

### Success Flash Message
```php
return redirect()->back()->with('success', 'Operation successful');
```

### Error Flash Message
```php
return redirect()->back()->with('error', 'Operation failed');
```

### Validation Errors
```php
return redirect()->back()->withErrors($validator)->withInput();
```

---

## Rate Limiting

Default Laravel rate limiting applies:
- 60 requests per minute for authenticated users
- 10 requests per minute for guests

---

<p align="center">
  <em>ClockWise API Reference v1.0</em><br>
  <em>Last Updated: January 2026</em>
</p>
