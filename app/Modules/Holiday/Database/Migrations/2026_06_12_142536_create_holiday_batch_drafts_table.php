<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('holiday_batch_drafts', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('wizard_id');
            $table->json('data');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('token');
            $table->index('user_id');
            $table->index('expires_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('holiday_batch_drafts');
    }
};
