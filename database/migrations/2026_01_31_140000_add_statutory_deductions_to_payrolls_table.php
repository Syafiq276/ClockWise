<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds Malaysian statutory deduction fields to payrolls table
     */
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            // EPF/KWSP
            $table->decimal('epf_employee', 10, 2)->default(0)->after('gross_pay');
            $table->decimal('epf_employer', 10, 2)->default(0)->after('epf_employee');
            $table->decimal('epf_rate_employee', 5, 2)->default(11)->after('epf_employer'); // Default 11%
            $table->decimal('epf_rate_employer', 5, 2)->default(12)->after('epf_rate_employee'); // Default 12%
            
            // SOCSO/PERKESO
            $table->decimal('socso_employee', 10, 2)->default(0)->after('epf_rate_employer');
            $table->decimal('socso_employer', 10, 2)->default(0)->after('socso_employee');
            
            // EIS/SIP
            $table->decimal('eis_employee', 10, 2)->default(0)->after('socso_employer');
            $table->decimal('eis_employer', 10, 2)->default(0)->after('eis_employee');
            
            // PCB/MTD (Monthly Tax Deduction) - optional manual entry
            $table->decimal('pcb', 10, 2)->default(0)->after('eis_employer');
            
            // Total statutory deductions (employee portion)
            $table->decimal('total_statutory', 10, 2)->default(0)->after('pcb');
            
            // Employer total contribution
            $table->decimal('employer_contribution', 10, 2)->default(0)->after('total_statutory');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn([
                'epf_employee',
                'epf_employer',
                'epf_rate_employee',
                'epf_rate_employer',
                'socso_employee',
                'socso_employer',
                'eis_employee',
                'eis_employer',
                'pcb',
                'total_statutory',
                'employer_contribution',
            ]);
        });
    }
};
