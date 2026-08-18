<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payroll_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies', 'id')->onDelete('cascade');
            $table->index('company_id');
            $table->string('name');
            $table->string('type');
            $table->string('effect')->default('addition');
            $table->text('description')->nullable();
            $table->string('country_code')->default('US');
            $table->string('state_code')->nullable();
            $table->text('calculation_logic');
            $table->boolean('is_statutory')->default(false);
            $table->date('effective_date');
            $table->date('expiry_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('parent_policy_id')->nullable()->constrained('payroll_policies', 'id')->onDelete('set null');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();

            			$table->index('name');
			$table->index('type');
			$table->index('country_code');
			$table->index('state_code');
			$table->index('is_active');
			$table->index('effective_date');
			$table->index('expiry_date');
			$table->index('parent_policy_id');
			$table->index('is_statutory');
			$table->index('deleted_at');
			$table->index(['type', 'country_code']);
			$table->index(['country_code', 'state_code']);
			$table->index(['type', 'is_active']);
			$table->index(['effective_date', 'expiry_date']);
			$table->index(['country_code', 'effective_date']);
			$table->unique(['name', 'type', 'country_code']);
			$table->unique('name');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_policies');
    }
};
