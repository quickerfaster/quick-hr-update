<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('loan_types')) {
            Schema::create('loan_types', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->decimal('min_amount', 15, 2)->default(0);
                $table->decimal('max_amount', 15, 2)->default(0);
                $table->decimal('default_interest_rate', 5, 2)->default(0)->comment('Annual percentage rate');
                $table->integer('max_repayment_months')->default(12);
                $table->boolean('requires_approval')->default(true);
                $table->boolean('is_active')->default(true);
                $table->json('eligibility_rules')->nullable()->comment('min_tenure_months, max_salary_percent, etc.');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_types');
    }
};
