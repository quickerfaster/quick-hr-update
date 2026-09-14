<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add a unique constraint to prevent duplicate clock events.
     *
     * An employee cannot have two clock events of the same type at the
     * exact same timestamp. This is the database-level safety net for
     * the application-level idempotency check in ClockEventRecorderService.
     *
     * Soft-deleted records are excluded from the unique constraint so
     * that deleted duplicates don't block legitimate re-creation.
     */
    public function up()
    {
        // Clean up any existing exact duplicates before adding the constraint.
        // Keep the earliest (lowest ID) record for each duplicate group.
        // Uses per-group deletion compatible with SQLite, MySQL, and PostgreSQL.
        $duplicates = DB::table('clock_events')
            ->select('employee_id', 'event_type', 'timestamp', DB::raw('MIN(id) as keep_id'))
            ->whereNull('deleted_at')
            ->groupBy('employee_id', 'event_type', 'timestamp')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('clock_events')
                ->where('employee_id', $dup->employee_id)
                ->where('event_type', $dup->event_type)
                ->where('timestamp', $dup->timestamp)
                ->where('id', '!=', $dup->keep_id)
                ->whereNull('deleted_at')
                ->delete();
        }

        Schema::table('clock_events', function (Blueprint $table) {
            $table->unique(
                ['employee_id', 'event_type', 'timestamp'],
                'clock_events_employee_event_time_unique'
            );
        });
    }

    public function down()
    {
        Schema::table('clock_events', function (Blueprint $table) {
            $table->dropUnique('clock_events_employee_event_time_unique');
        });
    }
};
