<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payroll_run_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies', 'id')->onDelete('cascade');
            $table->index('company_id');
            $table->foreignId('payroll_run_id')->constrained('payroll_runs', 'id')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees', 'id')->onDelete('restrict');
            $table->string('type');
            $table->string('label');
            $table->decimal('amount', 12, 2);
            $table->text('note')->nullable();
            $table->string('source_type')->default('manual')->nullable();
            $table->integer('source_id')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();

            			$table->index('payroll_run_id');
			$table->index('employee_id');
			$table->index('type');
			$table->index('amount');
			$table->index('created_at');
			$table->index('deleted_at');
			$table->index(['payroll_run_id', 'employee_id']);
			$table->index(['payroll_run_id', 'type']);
			$table->index(['employee_id', 'type']);
			$table->index(['payroll_run_id', 'created_at']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_run_adjustments');
    }
};
