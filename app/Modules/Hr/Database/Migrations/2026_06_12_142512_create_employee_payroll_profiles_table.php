<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employee_payroll_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies', 'id')->onDelete('cascade');
            $table->index('company_id');
            $table->foreignId('employee_id')->constrained('employees', 'id')->onDelete('cascade');
            $table->foreignId('pay_schedule_id')->constrained('pay_schedules', 'id')->onDelete('restrict');
            $table->string('bank_name')->nullable();
            $table->string('account_type')->default('checking')->nullable();
            $table->text('bank_sort_code')->nullable();
            $table->text('bank_code')->nullable();
            $table->text('bank_account_name')->nullable();
            $table->text('bank_account_number')->nullable();
            $table->text('bank_routing_number')->nullable();
            $table->text('bank_iban')->nullable();
            $table->text('bank_swift')->nullable();
            $table->string('payment_method')->default('bank_transfer');
            $table->string('tax_filing_status')->nullable();
            $table->integer('allowances')->default(0)->nullable();
            $table->decimal('extra_withholding', 10, 2)->default(0)->nullable();
            $table->boolean('is_exempt_from_federal_tax')->default(false)->nullable();
            $table->string('override_country_code')->default('US')->nullable();
            $table->string('override_state_code')->nullable();
            $table->string('currency_code')->nullable();
            $table->date('effective_date');
            $table->date('expiry_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();

            			$table->index('employee_id');
			$table->index('pay_schedule_id');
			$table->index('is_active');
			$table->index('effective_date');
			$table->index('expiry_date');
			$table->index('payment_method');
			$table->index('override_country_code');
			$table->index('currency_code');
			$table->index('deleted_at');
			$table->index(['employee_id', 'is_active']);
			$table->index(['pay_schedule_id', 'is_active']);
			$table->index(['override_country_code', 'is_active']);
			$table->unique('employee_id');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_payroll_profiles');
    }
};
