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
        Schema::table('users', function (Blueprint $table) {
            
        $table->enum('role',['admin','employee'])->default('employee');
        $table->decimal('hourly_rate', 8,2)->default(0.00);
        $table->string('position')->nullable(); 
        $table->softDeletes();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'hourly_rate', 'position']);
            $table->dropSoftDeletes();
        });
    }
};
