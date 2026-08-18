<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies', 'id')->onDelete('cascade');
            $table->index('company_id');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('color')->default('primary');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            			$table->index('name');
			$table->index('slug');
			$table->index('color');
			$table->index('is_active');
			$table->index(['color', 'is_active']);
			$table->unique('name');
			$table->unique('slug');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tags');
    }
};
