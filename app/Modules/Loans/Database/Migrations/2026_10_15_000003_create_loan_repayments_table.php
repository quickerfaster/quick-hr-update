<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('loan_repayments')) {
            Schema::create('loan_repayments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
                $table->foreignId('payroll_run_id')->nullable()->constrained('payroll_runs')->nullOnDelete();
                $table->decimal('amount', 15, 2);
                $table->decimal('principal', 15, 2)->default(0);
                $table->decimal('interest', 15, 2)->default(0);
                $table->date('due_date');
                $table->date('paid_date')->nullable();
                $table->string('status')->default('pending')->comment('pending, paid, overdue, waived');
                $table->string('payment_method')->nullable()->comment('payroll_deduction, manual, bank_transfer');
                $table->string('reference')->nullable()->comment('Transaction reference');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_repayments');
    }
};
