<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employee_job_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies', 'id')->onDelete('cascade');
            $table->index('company_id');
            $table->foreignId('employee_id')->constrained('employees', 'id')->onDelete('cascade');
            $table->date('effective_date');
            $table->date('end_date')->nullable();
            $table->string('change_reason');
            $table->text('notes')->nullable();
            $table->string('job_title');
            $table->string('department');
            $table->string('manager_name')->nullable();
            $table->string('pay_type');
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('base_salary', 10, 2)->nullable();
            $table->string('salary_currency')->default('USD');
            $table->string('pay_frequency');
            $table->string('employment_status');
            $table->string('location')->nullable();
            $table->string('shift')->nullable();
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            			$table->index('employee_id');
   $table->index('effective_date');
   $table->index('change_reason');
   $table->index('employment_status');
   $table->index(['employee_id', 'effective_date']);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_job_histories');
    }
};
