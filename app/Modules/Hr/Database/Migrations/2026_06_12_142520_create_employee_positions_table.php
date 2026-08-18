<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employee_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies', 'id')->onDelete('cascade');
            $table->index('company_id');
            $table->foreignId('employee_id')->constrained('employees', 'id')->onDelete('cascade');
            $table->foreignId('job_title_id')->constrained('job_titles', 'id')->onDelete('restrict');
            $table->foreignId('department_id')->constrained('departments', 'id')->onDelete('restrict');
            $table->foreignId('manager_id')->nullable()->constrained('employees', 'id')->onDelete('set null');
            $table->foreignId('reports_to')->nullable()->constrained('employees', 'id')->onDelete('set null');
            $table->string('employment_status')->default('Active');
            $table->string('pay_type');
            $table->decimal('hourly_rate', 10, 2)->default(0)->nullable();
            $table->decimal('base_salary', 10, 2)->default(0)->nullable();
            $table->string('salary_currency')->default('USD');
            $table->string('pay_frequency')->default('Monthly');
            $table->foreignId('pay_schedule_id')->nullable()->constrained('pay_schedules', 'id')->onDelete('restrict');
            $table->foreignId('location_id')->nullable()->constrained('locations', 'id')->onDelete('restrict');
            $table->foreignId('shift_id')->nullable()->constrained('shifts', 'id')->onDelete('restrict');
            $table->foreignId('attendance_policy_id')->nullable()->constrained('attendance_policies', 'id')->onDelete('restrict');
            $table->string('cost_center')->nullable();
            $table->string('work_email')->nullable();
            $table->string('work_phone_extension')->nullable();
            $table->text('job_description')->nullable();

            			$table->index('employee_id');
			$table->index('employment_status');
			$table->index('department_id');
			$table->index('location_id');
			$table->index('pay_schedule_id');
			$table->index(['pay_schedule_id', 'employment_status']);
			$table->index(['department_id', 'employment_status']);
			$table->index(['location_id', 'employment_status']);
			$table->unique('employee_id');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_positions');
    }
};
