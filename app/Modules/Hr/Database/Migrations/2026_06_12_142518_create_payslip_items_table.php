<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payslip_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies', 'id')->onDelete('cascade');
            $table->index('company_id');
            $table->foreignId('payslip_id')->constrained('payroll_payslips', 'id')->onDelete('cascade');
            $table->string('type');
            $table->string('label');
            $table->decimal('amount', 12, 2);
            $table->foreignId('policy_id')->nullable()->constrained('payroll_policies', 'id')->onDelete('set null');
            $table->foreignId('adjustment_id')->nullable()->constrained('payroll_run_adjustments', 'id')->onDelete('set null');
            $table->foreignId('employee_adjustment_profile_id')->nullable()->constrained('employee_adjustment_profiles', 'id')->onDelete('set null');
            $table->json('calculation_metadata')->nullable();

            			$table->index('payslip_id');
			$table->index('type');
			$table->index('amount');
			$table->index('policy_id');
			$table->index('adjustment_id');
			$table->index('employee_adjustment_profile_id');
			$table->index(['payslip_id', 'type']);
			$table->index(['type', 'amount']);
			$table->index(['policy_id', 'payslip_id']);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payslip_items');
    }
};
