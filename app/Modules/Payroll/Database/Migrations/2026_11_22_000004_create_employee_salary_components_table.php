<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_salary_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies', 'id')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees', 'id')->onDelete('cascade');
            $table->string('component_key', 50);
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('currency_code', 3)->default('NGN');
            $table->date('effective_date');
            $table->date('expiry_date')->nullable();
            $table->timestamps();

            $table->index('employee_id');
            $table->index('component_key');
            $table->index('effective_date');
            $table->index(['employee_id', 'component_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_salary_components');
    }
};
