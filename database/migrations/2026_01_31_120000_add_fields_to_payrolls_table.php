<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->date('period_start')->after('month_year');
            $table->date('period_end')->after('period_start');
            $table->integer('days_worked')->default(0)->after('period_end');
            $table->decimal('hourly_rate', 8, 2)->default(0)->after('total_hours');
            $table->decimal('overtime_hours', 8, 2)->default(0)->after('hourly_rate');
            $table->decimal('overtime_pay', 10, 2)->default(0)->after('overtime_hours');
            $table->decimal('deductions', 10, 2)->default(0)->after('gross_pay');
            $table->text('deduction_notes')->nullable()->after('deductions');
            $table->decimal('allowances', 10, 2)->default(0)->after('deduction_notes');
            $table->text('allowance_notes')->nullable()->after('allowances');
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft')->after('net_pay');
            $table->foreignId('generated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('paid_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn([
                'period_start', 'period_end', 'days_worked', 'hourly_rate',
                'overtime_hours', 'overtime_pay', 'deductions', 'deduction_notes',
                'allowances', 'allowance_notes', 'status', 'generated_by', 'paid_at'
            ]);
        });
    }
};
