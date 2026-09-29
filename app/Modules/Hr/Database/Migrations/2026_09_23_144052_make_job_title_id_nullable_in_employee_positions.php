<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Makes job_title_id nullable so that minimal/placeholder
     * EmployeePosition records can be created during onboarding
     * before a job title is assigned (e.g. autoCreatePosition
     * in Step1EmployeeRecord).
     */
    public function up(): void
    {
        Schema::table('employee_positions', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['job_title_id']);
            // Make the column nullable
            $table->unsignedBigInteger('job_title_id')->nullable()->change();
            // Re-add the foreign key constraint
            $table->foreign('job_title_id')->references('id')->on('job_titles')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_positions', function (Blueprint $table) {
            $table->dropForeign(['job_title_id']);
            $table->unsignedBigInteger('job_title_id')->nullable(false)->change();
            $table->foreign('job_title_id')->references('id')->on('job_titles')->onDelete('restrict');
        });
    }
};
