<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Malaysian Employment Act 1955:
     * - Less than 2 years service: 8 days annual leave
     * - 2-5 years service: 12 days annual leave
     * - More than 5 years service: 16 days annual leave
     * 
     * We'll default to 12 days (configurable per employee)
     * MC is typically 14-22 days depending on service
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('annual_leave_entitlement')->default(12);
            $table->unsignedTinyInteger('mc_entitlement')->default(14);
            $table->date('employment_start_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['annual_leave_entitlement', 'mc_entitlement', 'employment_start_date']);
        });
    }
};
