<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
        // Skip if column already removed (idempotent).
        if (!Schema::hasColumn('employee_positions', 'pay_schedule_id')) {
            return;
        }

        // Use raw SQL for reliable cross-driver behaviour.
        // MySQL: dropping the FK automatically drops the associated index.
        // SQLite: FK and index must be dropped separately.
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            // Drop the foreign key (this also drops the index in MySQL).
            // Use individual try-catch since MySQL < 8.0 doesn't support IF EXISTS.
            try { DB::statement('ALTER TABLE employee_positions DROP FOREIGN KEY employee_positions_pay_schedule_id_foreign'); } catch (\Throwable) {}
            try { DB::statement('ALTER TABLE employee_positions DROP INDEX employee_positions_pay_schedule_id_index'); } catch (\Throwable) {}
            try { DB::statement('ALTER TABLE employee_positions DROP INDEX employee_positions_pay_schedule_id_employment_status_index'); } catch (\Throwable) {}
            // Drop the column.
            DB::statement('ALTER TABLE employee_positions DROP COLUMN pay_schedule_id');
        } else {
            // SQLite path.
            Schema::table('employee_positions', function (Blueprint $table) {
                try { $table->dropForeign(['pay_schedule_id']); } catch (\Throwable) {}
                try { $table->dropIndex('employee_positions_pay_schedule_id_index'); } catch (\Throwable) {}
                try { $table->dropIndex('employee_positions_pay_schedule_id_employment_status_index'); } catch (\Throwable) {}
                $table->dropColumn('pay_schedule_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_positions', function (Blueprint $table) {
            $table->unsignedBigInteger('pay_schedule_id')->nullable()->after('employee_id');
            $table->foreign('pay_schedule_id')->references('id')->on('pay_schedules')->onDelete('set null');
            $table->index('pay_schedule_id');
            $table->index(['pay_schedule_id', 'employment_status']);
        });
    }
};
