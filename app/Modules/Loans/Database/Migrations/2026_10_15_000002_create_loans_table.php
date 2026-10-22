<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('loans')) {
            Schema::create('loans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->foreignId('loan_type_id')->nullable()->constrained('loan_types')->nullOnDelete();
                $table->string('loan_number')->unique()->comment('Auto-generated: LN-2026-00001');
                $table->decimal('amount', 15, 2);
                $table->decimal('interest_rate', 5, 2)->default(0)->comment('Annual percentage at time of request');
                $table->integer('repayment_months')->default(1);
                $table->decimal('monthly_installment', 15, 2)->default(0)->comment('Calculated EMI');
                $table->decimal('total_repayable', 15, 2)->default(0)->comment('Principal + Interest');
                $table->decimal('paid_amount', 15, 2)->default(0);
                $table->decimal('balance', 15, 2)->default(0)->comment('Remaining amount');
                $table->string('status')->default('draft')->comment('draft, pending_approval, approved, rejected, active, completed, cancelled');
                $table->string('reason')->nullable()->comment('Employee stated reason');
                $table->text('notes')->nullable()->comment('Admin notes');
                $table->date('request_date')->nullable();
                $table->date('approval_date')->nullable();
                $table->date('start_date')->nullable()->comment('First repayment date');
                $table->date('expected_end_date')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->json('repayment_schedule')->nullable()->comment('Cached schedule for quick display');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};