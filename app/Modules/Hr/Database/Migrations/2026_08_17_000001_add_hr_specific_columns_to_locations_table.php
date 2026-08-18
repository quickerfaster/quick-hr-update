<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('locations', 'geofence_radius')) {
            Schema::table('locations', function (Blueprint $table) {
                $table->decimal('geofence_radius', 6, 2)->default(100)->nullable();
            });
        }

        if (!Schema::hasColumn('locations', 'external_id')) {
            Schema::table('locations', function (Blueprint $table) {
                $table->string('external_id')->nullable();
            });
        }

        if (!Schema::hasColumn('locations', 'last_synced_at')) {
            Schema::table('locations', function (Blueprint $table) {
                $table->datetime('last_synced_at')->nullable();
            });
        }

        if (!Schema::hasColumn('locations', 'employee_count')) {
            Schema::table('locations', function (Blueprint $table) {
                $table->integer('employee_count')->default(0);
            });
        }

        if (!Schema::hasColumn('locations', 'department_count')) {
            Schema::table('locations', function (Blueprint $table) {
                $table->integer('department_count')->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $columns = ['geofence_radius', 'external_id', 'last_synced_at', 'employee_count', 'department_count'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('locations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
