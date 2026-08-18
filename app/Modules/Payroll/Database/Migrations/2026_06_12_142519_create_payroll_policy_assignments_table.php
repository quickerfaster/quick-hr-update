<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payroll_policy_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies', 'id')->onDelete('cascade');
            $table->index('company_id');
            $table->foreignId('payroll_policy_id')->constrained('payroll_policies', 'id')->onDelete('restrict');
            $table->string('assignable_type');
            $table->integer('assignable_id');
            $table->integer('priority')->default(0);
            $table->date('effective_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();

            			$table->index('payroll_policy_id');
			$table->index('assignable_type');
			$table->index('assignable_id');
			$table->index('priority');
			$table->index('is_active');
			$table->index('effective_date');
			$table->index('expiry_date');
			$table->index('deleted_at');
			$table->index(['assignable_type', 'assignable_id']);
			$table->index(['assignable_type', 'is_active']);
			$table->index(['effective_date', 'expiry_date']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_policy_assignments');
    }
};
