<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies', 'id')->onDelete('cascade');
            $table->index('company_id');
            $table->foreignId('employee_id')->constrained('employees', 'id')->onDelete('cascade');
            $table->string('name');
            $table->string('type');
            $table->string('document');
            $table->date('uploaded_at')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('description')->nullable();

            			$table->index('employee_id');
			$table->index('type');
			$table->index('uploaded_at');
			$table->index('expiry_date');
			$table->index('deleted_at');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('documents');
    }
};
