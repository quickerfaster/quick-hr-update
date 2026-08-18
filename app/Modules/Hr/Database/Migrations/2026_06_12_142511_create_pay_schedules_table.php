<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pay_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies', 'id')->onDelete('cascade');
            $table->index('company_id');
            $table->string('name');
            $table->string('code');
            $table->string('frequency');
            $table->text('description')->nullable();
            $table->date('first_period_start_date');
            $table->date('next_pay_date');
            $table->integer('payment_delay_days')->default(0)->nullable();
            $table->string('country_code')->default('US');
            $table->string('state_code')->nullable();
            $table->string('currency_code')->default('USD');
            $table->string('timezone')->default('America/New_York');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();

            			$table->index('name');
			$table->index('code');
			$table->index('frequency');
			$table->index('is_active');
			$table->index('is_default');
			$table->index('country_code');
			$table->index('currency_code');
			$table->index('next_pay_date');
			$table->index('deleted_at');
			$table->index(['is_active', 'is_default']);
			$table->index(['frequency', 'is_active']);
			$table->index(['country_code', 'is_active']);
			$table->unique('name');
			$table->unique('code');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pay_schedules');
    }
};
