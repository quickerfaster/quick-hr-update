<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies', 'id')->onDelete('cascade');
            $table->index('company_id');
            $table->foreignId('employee_id')->constrained('employees', 'id')->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained('leave_types', 'id')->onDelete('cascade');
            $table->decimal('balance', 6, 2)->default(0);
            $table->decimal('accrual_rate', 6, 2)->default(1)->nullable();
            $table->string('accrual_frequency')->default('Monthly');
            $table->integer('year');

            			$table->index('employee_id');
			$table->index('leave_type_id');
			$table->index('year');
			$table->index('balance');
			$table->index('deleted_at');
			$table->index(['employee_id', 'leave_type_id', 'year']);
			$table->index(['leave_type_id', 'year']);
			$table->unique(['employee_id', 'leave_type_id', 'year']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_balances');
    }
};
