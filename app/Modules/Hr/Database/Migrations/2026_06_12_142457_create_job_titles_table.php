<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('job_titles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies', 'id')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();

            			$table->index('company_id');
   $table->index('title');
			$table->index('deleted_at');
			$table->unique('title');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('job_titles');
    }
};
