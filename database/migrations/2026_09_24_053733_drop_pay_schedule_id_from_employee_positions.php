<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Drops pay_schedule_id from employee_positions. The canonical
     * link between employees and pay schedules is now exclusively
     * through employee_payroll_profiles.
     */
    public function up(): void
    {
        Schema::table('employee_positions', function (Blueprint $table) {
            $table->dropIndex('employee_positions_pay_schedule_id_index');
            $table->dropForeign(['pay_schedule_id']);
            $table->dropColumn('pay_schedule_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_positions', function (Blueprint $table) {
            $table->foreignId('pay_schedule_id')->nullable()->constrained('pay_schedules', 'id')->onDelete('restrict');
            $table->index('pay_schedule_id');
            $table->index(['pay_schedule_id', 'employment_status']);
        });
    }
};
