<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies', 'id')->onDelete('cascade');
            $table->index('company_id');
            $table->foreignId('employee_id')->constrained('employees', 'id')->onDelete('cascade');
            $table->string('photo')->nullable();
            $table->string('preferred_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('nationality')->nullable();
            $table->string('marital_status')->nullable();
            $table->text('bio')->nullable();
            $table->string('personal_email');
            $table->string('personal_phone')->nullable();
            $table->string('work_phone')->nullable();
            $table->string('address_street')->nullable();
            $table->string('address_city')->nullable();
            $table->string('address_state')->nullable();
            $table->string('address_postal_code')->nullable();
            $table->string('address_country')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->string('passport_number')->nullable();
            $table->date('passport_expiry_date')->nullable();
            $table->string('national_id_number')->nullable();

            			$table->index('employee_id');
			$table->index('personal_email');
			$table->index('date_of_birth');
			$table->index('gender');
			$table->index('deleted_at');
			$table->index(['employee_id', 'deleted_at']);
			$table->unique('employee_id');
			$table->unique('personal_email');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_profiles');
    }
};
