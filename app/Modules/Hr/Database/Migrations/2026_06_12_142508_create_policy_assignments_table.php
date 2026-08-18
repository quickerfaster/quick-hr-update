<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('policy_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies', 'id')->onDelete('cascade');
            $table->index('company_id');
            $table->foreignId('attendance_policy_id')->constrained('attendance_policies', 'id')->onDelete('restrict');
            $table->string('assignable_type', 255)->nullable();
            $table->integer('assignable_id')->nullable();
            $table->integer('priority')->default(0);

            			$table->index('assignable_type');
			$table->index('assignable_id');
			$table->index('attendance_policy_id');
			$table->index('priority');
			$table->index('deleted_at');
			$table->index(['assignable_type', 'assignable_id']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('policy_assignments');
    }
};
