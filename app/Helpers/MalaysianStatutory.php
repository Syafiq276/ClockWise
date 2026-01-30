<?php

namespace App\Helpers;

/**
 * Malaysian Statutory Deductions Calculator
 * 
 * Calculates EPF (KWSP), SOCSO (PERKESO), and EIS (SIP) contributions
 * Based on Malaysian law rates as of 2024
 */
class MalaysianStatutory
{
    /**
     * EPF Contribution Rates
     * Employee: 11% (can opt for 0% for age 60+)
     * Employer: 13% (salary ≤ RM5,000) or 12% (salary > RM5,000)
     */
    public static function calculateEPF(float $grossPay, ?int $employeeAge = null): array
    {
        // Employee rate: 11% (standard), can be reduced for age 60+
        $employeeRate = 11;
        if ($employeeAge !== null && $employeeAge >= 60) {
            $employeeRate = 0; // Optional - can choose to continue contributing
        }

        // Employer rate: 13% if salary ≤ RM5,000, else 12%
        $employerRate = $grossPay <= 5000 ? 13 : 12;

        $employeeContribution = round($grossPay * ($employeeRate / 100), 2);
        $employerContribution = round($grossPay * ($employerRate / 100), 2);

        return [
            'employee' => $employeeContribution,
            'employer' => $employerContribution,
            'employee_rate' => $employeeRate,
            'employer_rate' => $employerRate,
        ];
    }

    /**
     * SOCSO Contribution Table (Simplified)
     * Based on Employment Injury Scheme + Invalidity Scheme
     * For employees earning up to RM5,000/month (mandatory)
     * Above RM5,000 - voluntary but many employers still contribute
     */
    public static function calculateSOCSO(float $grossPay): array
    {
        // Simplified SOCSO table based on wage categories
        $socsoTable = [
            ['max' => 30, 'employee' => 0.10, 'employer' => 0.40],
            ['max' => 50, 'employee' => 0.20, 'employer' => 0.70],
            ['max' => 70, 'employee' => 0.30, 'employer' => 1.00],
            ['max' => 100, 'employee' => 0.40, 'employer' => 1.30],
            ['max' => 140, 'employee' => 0.60, 'employer' => 1.90],
            ['max' => 200, 'employee' => 0.85, 'employer' => 2.65],
            ['max' => 300, 'employee' => 1.25, 'employer' => 3.95],
            ['max' => 400, 'employee' => 1.75, 'employer' => 5.45],
            ['max' => 500, 'employee' => 2.25, 'employer' => 6.95],
            ['max' => 600, 'employee' => 2.75, 'employer' => 8.45],
            ['max' => 700, 'employee' => 3.25, 'employer' => 9.95],
            ['max' => 800, 'employee' => 3.75, 'employer' => 11.45],
            ['max' => 900, 'employee' => 4.25, 'employer' => 12.95],
            ['max' => 1000, 'employee' => 4.75, 'employer' => 14.45],
            ['max' => 1100, 'employee' => 5.25, 'employer' => 15.95],
            ['max' => 1200, 'employee' => 5.75, 'employer' => 17.45],
            ['max' => 1300, 'employee' => 6.25, 'employer' => 18.95],
            ['max' => 1400, 'employee' => 6.75, 'employer' => 20.45],
            ['max' => 1500, 'employee' => 7.25, 'employer' => 21.95],
            ['max' => 1600, 'employee' => 7.75, 'employer' => 23.45],
            ['max' => 1700, 'employee' => 8.25, 'employer' => 24.95],
            ['max' => 1800, 'employee' => 8.75, 'employer' => 26.45],
            ['max' => 1900, 'employee' => 9.25, 'employer' => 27.95],
            ['max' => 2000, 'employee' => 9.75, 'employer' => 29.45],
            ['max' => 2100, 'employee' => 10.25, 'employer' => 30.95],
            ['max' => 2200, 'employee' => 10.75, 'employer' => 32.45],
            ['max' => 2300, 'employee' => 11.25, 'employer' => 33.95],
            ['max' => 2400, 'employee' => 11.75, 'employer' => 35.45],
            ['max' => 2500, 'employee' => 12.25, 'employer' => 36.95],
            ['max' => 2600, 'employee' => 12.75, 'employer' => 38.45],
            ['max' => 2700, 'employee' => 13.25, 'employer' => 39.95],
            ['max' => 2800, 'employee' => 13.75, 'employer' => 41.45],
            ['max' => 2900, 'employee' => 14.25, 'employer' => 42.95],
            ['max' => 3000, 'employee' => 14.75, 'employer' => 44.45],
            ['max' => 3100, 'employee' => 15.25, 'employer' => 45.95],
            ['max' => 3200, 'employee' => 15.75, 'employer' => 47.45],
            ['max' => 3300, 'employee' => 16.25, 'employer' => 48.95],
            ['max' => 3400, 'employee' => 16.75, 'employer' => 50.45],
            ['max' => 3500, 'employee' => 17.25, 'employer' => 51.95],
            ['max' => 3600, 'employee' => 17.75, 'employer' => 53.45],
            ['max' => 3700, 'employee' => 18.25, 'employer' => 54.95],
            ['max' => 3800, 'employee' => 18.75, 'employer' => 56.45],
            ['max' => 3900, 'employee' => 19.25, 'employer' => 57.95],
            ['max' => 4000, 'employee' => 19.75, 'employer' => 59.45],
            ['max' => 4100, 'employee' => 20.25, 'employer' => 60.95],
            ['max' => 4200, 'employee' => 20.75, 'employer' => 62.45],
            ['max' => 4300, 'employee' => 21.25, 'employer' => 63.95],
            ['max' => 4400, 'employee' => 21.75, 'employer' => 65.45],
            ['max' => 4500, 'employee' => 22.25, 'employer' => 66.95],
            ['max' => 4600, 'employee' => 22.75, 'employer' => 68.45],
            ['max' => 4700, 'employee' => 23.25, 'employer' => 69.95],
            ['max' => 4800, 'employee' => 23.75, 'employer' => 71.45],
            ['max' => 4900, 'employee' => 24.25, 'employer' => 72.95],
            ['max' => 5000, 'employee' => 24.75, 'employer' => 74.45],
            // Above RM5,000 - use maximum category
            ['max' => PHP_INT_MAX, 'employee' => 24.75, 'employer' => 74.45],
        ];

        foreach ($socsoTable as $category) {
            if ($grossPay <= $category['max']) {
                return [
                    'employee' => $category['employee'],
                    'employer' => $category['employer'],
                ];
            }
        }

        // Default to max category
        return [
            'employee' => 24.75,
            'employer' => 74.45,
        ];
    }

    /**
     * EIS (Employment Insurance System) Contribution
     * Both employee and employer: 0.2% each
     * Maximum insurable salary: RM5,000
     */
    public static function calculateEIS(float $grossPay): array
    {
        // EIS caps at RM5,000
        $cappedSalary = min($grossPay, 5000);
        $rate = 0.2; // 0.2%

        // Simplified EIS table
        $eisTable = [
            ['max' => 30, 'contribution' => 0.05],
            ['max' => 50, 'contribution' => 0.10],
            ['max' => 70, 'contribution' => 0.15],
            ['max' => 100, 'contribution' => 0.20],
            ['max' => 140, 'contribution' => 0.25],
            ['max' => 200, 'contribution' => 0.35],
            ['max' => 300, 'contribution' => 0.50],
            ['max' => 400, 'contribution' => 0.70],
            ['max' => 500, 'contribution' => 0.90],
            ['max' => 600, 'contribution' => 1.10],
            ['max' => 700, 'contribution' => 1.30],
            ['max' => 800, 'contribution' => 1.50],
            ['max' => 900, 'contribution' => 1.70],
            ['max' => 1000, 'contribution' => 1.90],
            ['max' => 1100, 'contribution' => 2.10],
            ['max' => 1200, 'contribution' => 2.30],
            ['max' => 1300, 'contribution' => 2.50],
            ['max' => 1400, 'contribution' => 2.70],
            ['max' => 1500, 'contribution' => 2.90],
            ['max' => 1600, 'contribution' => 3.10],
            ['max' => 1700, 'contribution' => 3.30],
            ['max' => 1800, 'contribution' => 3.50],
            ['max' => 1900, 'contribution' => 3.70],
            ['max' => 2000, 'contribution' => 3.90],
            ['max' => 2100, 'contribution' => 4.10],
            ['max' => 2200, 'contribution' => 4.30],
            ['max' => 2300, 'contribution' => 4.50],
            ['max' => 2400, 'contribution' => 4.70],
            ['max' => 2500, 'contribution' => 4.90],
            ['max' => 2600, 'contribution' => 5.10],
            ['max' => 2700, 'contribution' => 5.30],
            ['max' => 2800, 'contribution' => 5.50],
            ['max' => 2900, 'contribution' => 5.70],
            ['max' => 3000, 'contribution' => 5.90],
            ['max' => 3100, 'contribution' => 6.10],
            ['max' => 3200, 'contribution' => 6.30],
            ['max' => 3300, 'contribution' => 6.50],
            ['max' => 3400, 'contribution' => 6.70],
            ['max' => 3500, 'contribution' => 6.90],
            ['max' => 3600, 'contribution' => 7.10],
            ['max' => 3700, 'contribution' => 7.30],
            ['max' => 3800, 'contribution' => 7.50],
            ['max' => 3900, 'contribution' => 7.70],
            ['max' => 4000, 'contribution' => 7.90],
            ['max' => 4100, 'contribution' => 8.10],
            ['max' => 4200, 'contribution' => 8.30],
            ['max' => 4300, 'contribution' => 8.50],
            ['max' => 4400, 'contribution' => 8.70],
            ['max' => 4500, 'contribution' => 8.90],
            ['max' => 4600, 'contribution' => 9.10],
            ['max' => 4700, 'contribution' => 9.30],
            ['max' => 4800, 'contribution' => 9.50],
            ['max' => 4900, 'contribution' => 9.70],
            ['max' => 5000, 'contribution' => 9.90],
            ['max' => PHP_INT_MAX, 'contribution' => 9.90], // Max cap
        ];

        foreach ($eisTable as $category) {
            if ($cappedSalary <= $category['max']) {
                return [
                    'employee' => $category['contribution'],
                    'employer' => $category['contribution'],
                ];
            }
        }

        return [
            'employee' => 9.90,
            'employer' => 9.90,
        ];
    }

    /**
     * Calculate all statutory deductions
     */
    public static function calculateAll(float $grossPay, ?int $employeeAge = null): array
    {
        $epf = self::calculateEPF($grossPay, $employeeAge);
        $socso = self::calculateSOCSO($grossPay);
        $eis = self::calculateEIS($grossPay);

        $totalEmployee = $epf['employee'] + $socso['employee'] + $eis['employee'];
        $totalEmployer = $epf['employer'] + $socso['employer'] + $eis['employer'];

        return [
            'epf' => $epf,
            'socso' => $socso,
            'eis' => $eis,
            'total_employee' => round($totalEmployee, 2),
            'total_employer' => round($totalEmployer, 2),
        ];
    }
}
