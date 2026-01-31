# 📖 ClockWise User Manual

## Table of Contents

1. [Getting Started](#1-getting-started)
2. [Employee Guide](#2-employee-guide)
3. [Admin Guide](#3-admin-guide)
4. [Troubleshooting](#4-troubleshooting)

---

## 1. Getting Started

### 1.1 Accessing the System

1. Open your web browser (Chrome, Firefox, Edge recommended)
2. Navigate to your ClockWise URL (e.g., `https://hrms.yourcompany.com`)
3. You will see the login page

### 1.2 Logging In

1. Enter your **Email** address
2. Enter your **Password**
3. Click **Log In**

![Login Page](docs/images/login.png)

### 1.3 First Time Login

If this is your first login:
1. Use the credentials provided by your administrator
2. We recommend changing your password after first login
3. Contact admin if you haven't received your credentials

### 1.4 Forgot Password

1. Click **"Forgot your password?"** on the login page
2. Enter your registered email address
3. Click **Send Reset Link**
4. Check your email for the reset link
5. Click the link and enter your new password

---

## 2. Employee Guide

### 2.1 Dashboard Overview

After logging in, you'll see your personal dashboard with:

| Section | Description |
|---------|-------------|
| **Clock Widget** | Clock in/out button with current status |
| **Today's Status** | Your attendance status for today |
| **Leave Balance** | Remaining annual leave and MC days |
| **Recent Attendance** | Last 5 attendance records |

### 2.2 Clocking In

1. From the dashboard, locate the **Clock In** button
2. Click **Clock In** to record your arrival
3. The system will record:
   - Current time
   - Your location (Office/Remote based on IP)
   - Status (On-time or Late)

**Note:** You are considered "late" if you clock in after 9:15 AM (9:00 AM + 15 min grace period).

### 2.3 Clocking Out

1. Click the **Clock Out** button (appears after clocking in)
2. Your total work hours will be calculated automatically
3. Any overtime (beyond 6:00 PM) will be recorded

### 2.4 Viewing Attendance History

1. Click **"Attendance"** in the sidebar
2. View your monthly attendance records
3. Use filters to view specific months
4. Statistics shown:
   - Total days worked
   - On-time arrivals
   - Late arrivals
   - Total hours worked

### 2.5 Requesting Leave

#### Submitting a Leave Request

1. Click **"Leave"** in the sidebar
2. Click **"Request Leave"** button
3. Fill in the form:
   - **Leave Type**: Annual, MC, Emergency, or Unpaid
   - **Start Date**: First day of leave
   - **End Date**: Last day of leave
   - **Reason**: Brief explanation
   - **Attachment**: Upload MC certificate if applicable
4. Click **Submit Request**

#### Leave Types

| Type | Description | Documentation |
|------|-------------|---------------|
| Annual Leave | Planned vacation/personal time | Not required |
| Medical Leave (MC) | Sick leave | MC certificate required |
| Emergency Leave | Urgent unforeseen circumstances | Not required |
| Unpaid Leave | Leave without pay | Not required |

#### Checking Leave Status

- **Pending** 🟡 - Awaiting admin approval
- **Approved** 🟢 - Leave granted
- **Rejected** 🔴 - Leave not granted (check remarks)

#### Leave Balance

Your remaining leave is shown on:
- Dashboard (summary)
- Leave page (detailed)

**Default Entitlements:**
- Annual Leave: 12 days/year
- Medical Leave: 14 days/year

### 2.6 Viewing Payslips

1. Click **"Payslips"** in the sidebar
2. View list of all your payslips
3. Click on a payslip to see details:
   - Basic pay calculation
   - Overtime pay
   - Statutory deductions (EPF, SOCSO, EIS)
   - Net pay
4. Click **"Download PDF"** to save/print

#### Understanding Your Payslip

| Item | Description |
|------|-------------|
| **Basic Pay** | Hourly rate × Hours worked |
| **Overtime Pay** | Hourly rate × 1.5 × OT hours |
| **Gross Pay** | Basic + Overtime + Allowances |
| **EPF (11%)** | Employee's EPF contribution |
| **SOCSO** | Social security contribution |
| **EIS** | Employment insurance |
| **Net Pay** | Gross pay - All deductions |

---

## 3. Admin Guide

### 3.1 Admin Dashboard

The admin dashboard shows:
- Total employees
- Today's attendance count
- Pending leave requests
- Monthly statistics
- Quick action buttons

### 3.2 Managing Employees

#### Viewing Employees

1. Click **"Employees"** in the sidebar
2. See list of all employees with:
   - Name and email
   - Position
   - Hourly rate
   - Status

#### Adding New Employee

1. Click **"Add Employee"** button
2. Fill in the form:
   - Name
   - Email (will be login username)
   - Password (temporary)
   - Position
   - Hourly Rate (RM/hour)
   - Employment Start Date
3. Click **Create Employee**

**Note:** New employees will use this email and password to login.

#### Editing Employee

1. Click the **Edit** button next to employee
2. Update any details
3. Click **Save Changes**

#### Deleting Employee

1. Click the **Delete** button
2. Confirm the deletion
3. **Warning:** This will also delete all attendance and leave records

### 3.3 Attendance Management

#### Viewing Attendance Log

1. Click **"Attendance"** (under Admin section)
2. View all employee attendance
3. Use filters:
   - Date range
   - Employee name
   - Status (On-time/Late)

#### Attendance Reports

- Total clock-ins for the day
- Late arrivals highlighted
- Absent employees list

### 3.4 Leave Management

#### Reviewing Leave Requests

1. Click **"Leave Mgmt"** in sidebar
2. See all leave requests with status:
   - 🟡 Pending - Needs action
   - 🟢 Approved
   - 🔴 Rejected

#### Approving Leave

1. Click **"Review"** on a pending request
2. Review the details:
   - Employee name
   - Leave type and dates
   - Reason and attachment
   - Current leave balance
3. Click **"Approve"**
4. Leave balance will be automatically deducted

#### Rejecting Leave

1. Click **"Review"** on pending request
2. Enter rejection reason in **Admin Remarks**
3. Click **"Reject"**
4. Employee will see the rejection reason

### 3.5 Payroll Management

#### Generating Payroll

1. Click **"Payroll"** in sidebar
2. Click **"Generate Payroll"**
3. Select:
   - Month/Year
   - Employees (all or specific)
4. System calculates:
   - Hours from attendance
   - Overtime
   - Statutory deductions
5. Click **Generate**

#### Reviewing Payroll

Each payroll entry shows:
- Employee name
- Period
- Hours worked
- Gross/Net pay
- Status

#### Payroll Workflow

```
Draft → Approved → Paid
```

1. **Draft**: Just generated, can be edited
2. **Approved**: Verified, ready for payment
3. **Paid**: Payment completed

#### Approving Payroll

1. Review the payroll details
2. Click **"Approve"** to confirm
3. Click **"Mark as Paid"** after payment

### 3.6 Reports & Analytics

1. Click **"Reports"** in sidebar
2. Available reports:
   - **Attendance Summary**: Monthly attendance statistics
   - **Payroll Report**: Monthly payroll totals
   - **Employee Statistics**: Workforce overview

### 3.7 Audit Logs

1. Click **"Audit"** in sidebar
2. View security-related activities:
   - Login attempts
   - Employee changes
   - Leave approvals
   - Payroll actions

**Tracked Actions:**
- `auth.login_success` - Successful login
- `auth.login_failed` - Failed login attempt
- `employee.created` - New employee added
- `leave.approved` - Leave request approved
- `payroll.paid` - Payroll marked as paid

---

## 4. Troubleshooting

### Common Issues

#### Can't Login

| Problem | Solution |
|---------|----------|
| Wrong password | Use "Forgot Password" to reset |
| Account not found | Contact admin to verify email |
| Page not loading | Clear browser cache, try incognito |

#### Clock In/Out Not Working

1. Check your internet connection
2. Refresh the page
3. Make sure you're not already clocked in/out
4. Contact admin if issue persists

#### Leave Balance Incorrect

1. Check if recent leaves were approved
2. Balance resets annually
3. Contact admin for manual adjustment

#### Payslip Not Showing

1. Payroll may not be generated yet
2. Check with admin for payroll schedule
3. Previous months should be visible

#### PDF Download Not Working

1. Allow pop-ups in your browser
2. Try a different browser
3. Check if you have PDF viewer installed

### Getting Help

For technical issues:
1. Note the error message (if any)
2. Take a screenshot
3. Contact your system administrator
4. Provide: Date, time, what you were trying to do

---

## Quick Reference

### Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Enter` | Submit form |
| `Esc` | Close modal |

### Status Colors

| Color | Meaning |
|-------|---------|
| 🟢 Green | Success / Approved / On-time |
| 🟡 Yellow | Pending / Warning |
| 🔴 Red | Error / Rejected / Late |
| 🔵 Blue | Information |

### Contact

- **System Admin**: admin@yourcompany.com
- **HR Department**: hr@yourcompany.com

---

<p align="center">
  <em>ClockWise v1.0 - User Manual</em><br>
  <em>Last Updated: January 2026</em>
</p>
