<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('leave_approvers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies', 'id')->onDelete('cascade');
            $table->index('company_id');
            $table->foreignId('employee_id')->constrained('employees', 'id')->onDelete('cascade');
            $table->foreignId('approver_id')->constrained('employees', 'id')->onDelete('cascade');
            $table->integer('approval_level')->default(1);
            $table->boolean('can_approve_all_types')->default(true);
            $table->foreignId('leave_type_ids')->nullable()->constrained('leave_types', 'id');
            $table->integer('max_approval_days')->nullable();
            $table->boolean('is_active')->default(true);

            			$table->index('employee_id');
			$table->index('approver_id');
			$table->index('approval_level');
			$table->index('is_active');
			$table->index('deleted_at');
			$table->index(['employee_id', 'approval_level']);
			$table->index(['approver_id', 'is_active']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_approvers');
    }
};
