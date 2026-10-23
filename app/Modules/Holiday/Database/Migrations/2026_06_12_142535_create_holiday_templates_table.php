<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('holiday_templates', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 2)->nullable();
            $table->string('region_code', 10)->nullable();
            $table->string('name');
            $table->integer('month')->nullable();
            $table->integer('day')->nullable();
            $table->text('recurrence_rule')->nullable();
            $table->string('holiday_type')->default('public');
            $table->decimal('default_pay_rate', 4, 2)->default(1)->nullable();
            $table->boolean('is_paid')->default(true);
            $table->boolean('is_active')->default(true);

            $table->index('country_code');
            $table->index('region_code');
            $table->index(['country_code', 'region_code']);
            $table->index(['country_code', 'is_active']);
            $table->index('holiday_type');
            $table->index('deleted_at');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('holiday_templates');
    }
};
