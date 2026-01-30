<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // =============================================
        // USERS
        // =============================================
        
        // Create Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@clockwise.my'],
            [
                'name' => 'Ahmad Razak',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'position' => 'HR Manager',
                'hourly_rate' => 50.00,
                'annual_leave_entitlement' => 12,
                'mc_entitlement' => 14,
                'employment_start_date' => '2023-01-15',
            ]
        );

        // Create Sample Employees
        $employees = [
            [
                'email' => 'ali@clockwise.my',
                'name' => 'Ali bin Hassan',
                'position' => 'Software Developer',
                'hourly_rate' => 28.00,
                'employment_start_date' => '2024-03-01',
            ],
            [
                'email' => 'siti@clockwise.my',
                'name' => 'Siti Nurhaliza',
                'position' => 'UI/UX Designer',
                'hourly_rate' => 25.00,
                'employment_start_date' => '2024-06-15',
            ],
            [
                'email' => 'kumar@clockwise.my',
                'name' => 'Kumar a/l Rajan',
                'position' => 'Backend Developer',
                'hourly_rate' => 30.00,
                'employment_start_date' => '2023-09-01',
            ],
            [
                'email' => 'mei@clockwise.my',
                'name' => 'Tan Mei Ling',
                'position' => 'Project Manager',
                'hourly_rate' => 35.00,
                'employment_start_date' => '2023-05-20',
            ],
            [
                'email' => 'farid@clockwise.my',
                'name' => 'Farid Kamil',
                'position' => 'QA Engineer',
                'hourly_rate' => 22.00,
                'employment_start_date' => '2025-01-10',
            ],
        ];

        $createdEmployees = [];
        foreach ($employees as $emp) {
            $createdEmployees[] = User::updateOrCreate(
                ['email' => $emp['email']],
                [
                    'name' => $emp['name'],
                    'password' => Hash::make('password123'),
                    'role' => 'employee',
                    'position' => $emp['position'],
                    'hourly_rate' => $emp['hourly_rate'],
                    'annual_leave_entitlement' => 12,
                    'mc_entitlement' => 14,
                    'employment_start_date' => $emp['employment_start_date'],
                ]
            );
        }

        // =============================================
        // SETTINGS
        // =============================================
        
        $settings = [
            ['key' => 'company_name', 'value' => 'TechCorp Sdn Bhd'],
            ['key' => 'company_address', 'value' => 'Level 15, Menara KLCC, Kuala Lumpur'],
            ['key' => 'company_phone', 'value' => '+603-2181-8888'],
            ['key' => 'company_email', 'value' => 'hr@techcorp.com.my'],
            ['key' => 'work_start_time', 'value' => '09:00'],
            ['key' => 'work_end_time', 'value' => '18:00'],
            ['key' => 'late_threshold_minutes', 'value' => '15'],
            ['key' => 'overtime_rate_multiplier', 'value' => '1.5'],
            ['key' => 'weekend_rate_multiplier', 'value' => '2.0'],
            ['key' => 'kwsp_employer_rate', 'value' => '13'],
            ['key' => 'kwsp_employee_rate', 'value' => '11'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }

        // =============================================
        // ATTENDANCE RECORDS (Last 30 days)
        // =============================================
        
        $today = Carbon::now();
        
        foreach ($createdEmployees as $employee) {
            // Generate attendance for last 30 working days
            for ($i = 30; $i >= 0; $i--) {
                $date = $today->copy()->subDays($i);
                
                // Skip weekends
                if ($date->isWeekend()) {
                    continue;
                }

                // Random chance of absence (10%)
                if (rand(1, 100) <= 10) {
                    continue;
                }

                // Random clock in time (8:45 - 9:30)
                $clockInMinutes = rand(-15, 30);
                $clockIn = $date->copy()->setTime(9, 0)->addMinutes($clockInMinutes)->format('H:i:s');
                
                // Random clock out time (17:30 - 19:00)
                $clockOutMinutes = rand(-30, 60);
                $clockOut = $date->copy()->setTime(18, 0)->addMinutes($clockOutMinutes)->format('H:i:s');
                
                // Determine status
                $status = $clockInMinutes > 15 ? 'late' : 'ontime';
                
                // Random location type
                $locationType = rand(1, 100) <= 70 ? 'office' : 'remote';

                Attendance::updateOrCreate(
                    [
                        'user_id' => $employee->id,
                        'date' => $date->format('Y-m-d'),
                    ],
                    [
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'status' => $status,
                        'ip_address' => '192.168.1.' . rand(1, 255),
                        'location_type' => $locationType,
                    ]
                );
            }
        }

        // =============================================
        // LEAVE REQUESTS
        // =============================================
        
        // Past approved leaves
        $pastLeaves = [
            [
                'user_id' => $createdEmployees[0]->id,
                'type' => 'annual',
                'start_date' => $today->copy()->subDays(45),
                'end_date' => $today->copy()->subDays(43),
                'days' => 3,
                'reason' => 'Family vacation to Langkawi',
                'status' => 'approved',
            ],
            [
                'user_id' => $createdEmployees[1]->id,
                'type' => 'mc',
                'start_date' => $today->copy()->subDays(20),
                'end_date' => $today->copy()->subDays(19),
                'days' => 2,
                'reason' => 'Fever and flu',
                'status' => 'approved',
            ],
            [
                'user_id' => $createdEmployees[2]->id,
                'type' => 'annual',
                'start_date' => $today->copy()->subDays(60),
                'end_date' => $today->copy()->subDays(56),
                'days' => 5,
                'reason' => 'Deepavali celebration with family in Ipoh',
                'status' => 'approved',
            ],
            [
                'user_id' => $createdEmployees[3]->id,
                'type' => 'emergency',
                'start_date' => $today->copy()->subDays(15),
                'end_date' => $today->copy()->subDays(15),
                'days' => 1,
                'reason' => 'Family emergency - parent hospitalized',
                'status' => 'approved',
            ],
        ];

        // Pending leaves
        $pendingLeaves = [
            [
                'user_id' => $createdEmployees[0]->id,
                'type' => 'annual',
                'start_date' => $today->copy()->addDays(14),
                'end_date' => $today->copy()->addDays(18),
                'days' => 5,
                'reason' => 'Chinese New Year holiday',
                'status' => 'pending',
            ],
            [
                'user_id' => $createdEmployees[4]->id,
                'type' => 'annual',
                'start_date' => $today->copy()->addDays(7),
                'end_date' => $today->copy()->addDays(8),
                'days' => 2,
                'reason' => 'Personal matters',
                'status' => 'pending',
            ],
            [
                'user_id' => $createdEmployees[2]->id,
                'type' => 'mc',
                'start_date' => $today->copy()->subDays(1),
                'end_date' => $today->copy(),
                'days' => 2,
                'reason' => 'Food poisoning - MC attached',
                'status' => 'pending',
            ],
        ];

        // Rejected leave
        $rejectedLeaves = [
            [
                'user_id' => $createdEmployees[1]->id,
                'type' => 'annual',
                'start_date' => $today->copy()->subDays(30),
                'end_date' => $today->copy()->subDays(25),
                'days' => 6,
                'reason' => 'Extended holiday',
                'status' => 'rejected',
                'admin_remarks' => 'Project deadline conflict - please reschedule',
            ],
        ];

        foreach (array_merge($pastLeaves, $pendingLeaves, $rejectedLeaves) as $leave) {
            LeaveRequest::updateOrCreate(
                [
                    'user_id' => $leave['user_id'],
                    'start_date' => $leave['start_date']->format('Y-m-d'),
                ],
                [
                    'type' => $leave['type'],
                    'end_date' => $leave['end_date']->format('Y-m-d'),
                    'days' => $leave['days'],
                    'reason' => $leave['reason'],
                    'status' => $leave['status'],
                    'admin_remarks' => $leave['admin_remarks'] ?? null,
                    'approved_by' => $leave['status'] !== 'pending' ? $admin->id : null,
                    'responded_at' => $leave['status'] !== 'pending' ? now() : null,
                ]
            );
        }

        // =============================================
        // PAYROLL RECORDS (Last 3 months)
        // =============================================
        
        for ($monthOffset = 3; $monthOffset >= 1; $monthOffset--) {
            $payMonth = $today->copy()->subMonths($monthOffset);
            $periodStart = $payMonth->copy()->startOfMonth();
            $periodEnd = $payMonth->copy()->endOfMonth();
            
            foreach ($createdEmployees as $employee) {
                // Calculate working days (exclude weekends)
                $workingDays = 0;
                $currentDay = $periodStart->copy();
                while ($currentDay <= $periodEnd) {
                    if (!$currentDay->isWeekend()) {
                        $workingDays++;
                    }
                    $currentDay->addDay();
                }
                
                // Simulate some absences
                $daysWorked = $workingDays - rand(0, 2);
                $totalHours = $daysWorked * 8;
                $overtimeHours = rand(0, 20);
                
                $basicPay = $employee->hourly_rate * $totalHours;
                $overtimePay = $employee->hourly_rate * 1.5 * $overtimeHours;
                $grossPay = $basicPay + $overtimePay;
                
                // Malaysian statutory deductions (EPF/KWSP, SOCSO, EIS)
                $epfEmployee = round($grossPay * 0.11, 2);
                $epfEmployer = round($grossPay * 0.13, 2);
                $socsoEmployee = min(round($grossPay * 0.005, 2), 98.05);
                $socsoEmployer = min(round($grossPay * 0.0175, 2), 343.15);
                $eisEmployee = min(round($grossPay * 0.002, 2), 39.90);
                $eisEmployer = min(round($grossPay * 0.002, 2), 39.90);
                
                $totalStatutory = $epfEmployee + $socsoEmployee + $eisEmployee;
                $employerContribution = $epfEmployer + $socsoEmployer + $eisEmployer;
                $netPay = $grossPay - $totalStatutory;
                
                $status = $monthOffset === 1 ? 'approved' : 'paid';

                Payroll::updateOrCreate(
                    [
                        'user_id' => $employee->id,
                        'month_year' => $payMonth->format('Y-m'),
                    ],
                    [
                        'period_start' => $periodStart,
                        'period_end' => $periodEnd,
                        'days_worked' => $daysWorked,
                        'total_hours' => $totalHours,
                        'hourly_rate' => $employee->hourly_rate,
                        'overtime_hours' => $overtimeHours,
                        'overtime_pay' => $overtimePay,
                        'gross_pay' => $grossPay,
                        // EPF
                        'epf_employee' => $epfEmployee,
                        'epf_employer' => $epfEmployer,
                        'epf_rate_employee' => 11,
                        'epf_rate_employer' => 13,
                        // SOCSO
                        'socso_employee' => $socsoEmployee,
                        'socso_employer' => $socsoEmployer,
                        // EIS
                        'eis_employee' => $eisEmployee,
                        'eis_employer' => $eisEmployer,
                        // PCB (simplified - usually based on tax tables)
                        'pcb' => 0,
                        // Totals
                        'total_statutory' => $totalStatutory,
                        'employer_contribution' => $employerContribution,
                        // Other deductions/allowances
                        'deductions' => 0,
                        'allowances' => 0,
                        'net_pay' => $netPay,
                        'status' => $status,
                        'generated_by' => $admin->id,
                        'paid_at' => $status === 'paid' ? $periodEnd->copy()->addDays(5) : null,
                    ]
                );
            }
        }

        // =============================================
        // AUDIT LOGS
        // =============================================
        
        $auditActions = [
            ['action' => 'login', 'description' => 'User logged in successfully'],
            ['action' => 'clock_in', 'description' => 'Employee clocked in'],
            ['action' => 'clock_out', 'description' => 'Employee clocked out'],
            ['action' => 'leave_request', 'description' => 'Leave request submitted'],
            ['action' => 'leave_approved', 'description' => 'Leave request approved'],
            ['action' => 'payroll_generated', 'description' => 'Payroll generated for employee'],
            ['action' => 'employee_created', 'description' => 'New employee account created'],
            ['action' => 'settings_updated', 'description' => 'System settings updated'],
        ];

        // Generate audit logs for last 7 days
        for ($i = 7; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            
            // Random number of events per day
            $eventsCount = rand(5, 15);
            
            for ($j = 0; $j < $eventsCount; $j++) {
                $audit = $auditActions[array_rand($auditActions)];
                $user = rand(0, 1) ? $admin : $createdEmployees[array_rand($createdEmployees)];
                
                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => $audit['action'],
                    'description' => $audit['description'],
                    'ip_address' => '192.168.1.' . rand(1, 255),
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0',
                    'created_at' => $date->copy()->setTime(rand(8, 18), rand(0, 59)),
                ]);
            }
        }

        echo "\n✅ Database seeded successfully!\n";
        echo "\n📧 Login Credentials:\n";
        echo "   Admin:    admin@clockwise.my / password123\n";
        echo "   Employee: ali@clockwise.my / password123\n\n";
    }
}
