<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employee_adjustment_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies', 'id')->onDelete('cascade');
            $table->index('company_id');
            $table->foreignId('employee_id')->constrained('employees', 'id')->onDelete('cascade');
            $table->string('type');
            $table->string('label');
            $table->string('calculation_type');
            $table->decimal('value', 12, 2);
            $table->date('effective_date');
            $table->date('expiry_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('policy_id')->nullable()->constrained('payroll_policies', 'id')->onDelete('set null');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();

            			$table->index('employee_id');
			$table->index('type');
			$table->index('is_active');
			$table->index('effective_date');
			$table->index('expiry_date');
			$table->index('calculation_type');
			$table->index('policy_id');
			$table->index('deleted_at');
			$table->index(['employee_id', 'is_active']);
			$table->index(['type', 'is_active']);
			$table->index(['effective_date', 'expiry_date']);
			$table->unique(['employee_id', 'label', 'type']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_adjustment_profiles');
    }
};
