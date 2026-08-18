<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_number');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->foreignId('company_id')->nullable()->constrained('companies', 'id')->onDelete('set null');
            $table->foreignId('employee_group_id')->nullable()->constrained('employee_groups', 'id')->onDelete('set null');
            $table->date('hire_date');
            $table->foreignId('user_id')->nullable()->constrained('users', 'id')->onDelete('set null');
            $table->string('tag_ids')->nullable();
            
            			$table->index('employee_number');
			$table->index('email');
			$table->index('company_id');
			$table->index('employee_group_id');
			$table->index('user_id');
			$table->index('hire_date');
			$table->index('deleted_at');
			$table->index(['employee_number', 'company_id']);
			$table->index(['first_name', 'last_name']);
			$table->index(['company_id', 'hire_date']);
			$table->unique('employee_number');
			$table->unique('email');
			$table->unique('user_id');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
};
