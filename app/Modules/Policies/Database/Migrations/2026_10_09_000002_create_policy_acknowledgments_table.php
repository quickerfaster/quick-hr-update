<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('policy_acknowledgments')) {
            Schema::create('policy_acknowledgments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_policy_id')->constrained()->cascadeOnDelete();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('status')->default('pending');
                $table->timestamp('acknowledged_at')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->text('signature')->nullable();
                $table->timestamps();

                // One acknowledgment per employee per policy
                $table->unique(['company_policy_id', 'employee_id'], 'uq_policy_employee_ack');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_acknowledgments');
    }
};
