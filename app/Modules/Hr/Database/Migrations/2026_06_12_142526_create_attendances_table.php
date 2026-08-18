<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies', 'id')->onDelete('cascade');
            $table->index('company_id');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->foreignId('employee_id')->nullable()->constrained('employees', 'id')->onDelete('restrict');
            $table->string('company')->nullable();
            $table->string('department')->nullable();
            $table->date('date');
            $table->foreignId('shift_id')->nullable()->constrained('shifts', 'id')->onDelete('restrict');
            $table->string('status')->default('present');
            $table->decimal('net_hours', 10, 2)->default(0)->nullable(false);
            $table->string('absence_type')->nullable();
            $table->text('absence_reason')->nullable();
            $table->foreignId('leave_request_id')->nullable()->constrained('leave_requests', 'id')->onDelete('set null');
            $table->decimal('hours_deducted', 6, 2)->default(0)->nullable();
            $table->text('notes')->nullable();
            $table->json('sessions')->nullable();
            $table->string('approved_by')->nullable();
            $table->datetime('approved_at')->nullable();
            $table->datetime('last_calculated_at')->nullable();
            $table->string('calculation_method')->default('auto')->nullable();
            $table->foreignId('attendance_policy_id')->nullable()->constrained('attendance_policies', 'id')->onDelete('set null');
            $table->foreignId('work_pattern_id')->nullable()->constrained('work_patterns', 'id')->onDelete('set null');
            $table->json('calculation_metadata')->nullable();
            $table->string('calculation_version')->nullable();


            // Make numeric fields nullable (they currently have default 0)
            $table->decimal('regular_hours', 10, 2)->default(0)->nullable(false);
            $table->decimal('overtime_hours', 10, 2)->default(0)->nullable(false);
            $table->decimal('double_time_hours', 10, 2)->default(0)->nullable(false);
            $table->integer('minutes_late')->nullable();
            $table->integer('minutes_early_departure')->nullable();
            $table->integer('missed_break_minutes')->nullable();

            // Make boolean flags nullable (default values remain in code)
            $table->boolean('is_paid_absence')->nullable();
            $table->boolean('is_approved')->nullable();
            $table->boolean('needs_review')->nullable();
            $table->boolean('is_unplanned')->nullable();



            $table->index('employee_id');
			$table->index('date');
			$table->index('status');
			$table->index('is_approved');
			$table->index('needs_review');
			$table->index('leave_request_id');
			$table->index('deleted_at');
			$table->index(['employee_id', 'date']);
			$table->index(['status', 'date']);
			$table->index(['is_approved', 'date']);
			$table->index(['needs_review', 'date']);
			$table->unique(['employee_id', 'date']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendances');
    }
};
