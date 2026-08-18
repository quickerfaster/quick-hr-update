<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('work_patterns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies', 'id')->onDelete('cascade');
            $table->index('company_id');
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->string('pattern_type')->default('recurring');
            $table->integer('rotation_weeks')->default(2)->nullable();
            $table->foreignId('shift_id')->constrained('shifts', 'id')->onDelete('restrict');
            // $table->text('applicable_days');
            $table->string('applicable_days', 20);
            $table->time('override_start_time')->nullable();
            $table->time('override_end_time')->nullable();
            $table->date('effective_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('assigned_employee_count')->default(0);
            $table->date('last_used_date')->nullable();
            $table->integer('created_from_template_id')->nullable();

            			$table->index('code');
			$table->index('is_active');
			$table->index('is_default');
			$table->index('pattern_type');
			$table->index('shift_id');
			$table->index('effective_date');
			$table->index('deleted_at');
			$table->index(['is_active', 'is_default']);
			$table->index(['shift_id', 'pattern_type']);
			$table->index(['effective_date', 'end_date']);
			$table->unique(['name', 'shift_id']);
			$table->unique('code');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('work_patterns');
    }
};
