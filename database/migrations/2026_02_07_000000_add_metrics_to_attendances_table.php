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
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'total_hours')) {
                $table->decimal('total_hours', 8, 2)->default(0)->after('clock_out');
            }
            if (!Schema::hasColumn('attendances', 'overtime_hours')) {
                $table->decimal('overtime_hours', 8, 2)->default(0)->after('total_hours');
            }
            if (!Schema::hasColumn('attendances', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('attendances', 'overtime_hours')) {
                $table->dropColumn('overtime_hours');
            }
            if (Schema::hasColumn('attendances', 'total_hours')) {
                $table->dropColumn('total_hours');
            }
        });
    }
};
