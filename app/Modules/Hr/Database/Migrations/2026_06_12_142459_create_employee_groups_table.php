<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employee_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies', 'id')->onDelete('cascade');
            $table->index('company_id');
            $table->string('name');
            $table->string('code');
            $table->string('group_type')->default('manual');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('dynamic_rules')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();

            			$table->index('name');
			$table->index('code');
			$table->index('group_type');
			$table->index('is_active');
			$table->index('deleted_at');
			$table->index(['group_type', 'is_active']);
			$table->unique('name');
			$table->unique('code');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_groups');
    }
};
