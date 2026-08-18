<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('clock_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies', 'id')->onDelete('cascade');
            $table->index('company_id');
            $table->string('employee_number')->nullable();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->onDelete('restrict');
            $table->string('event_type');
            $table->datetime('timestamp');
            $table->string('method')->default('web');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_name')->nullable();
            $table->string('timezone')->default('UTC')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('device_id')->nullable();
            $table->string('device_name')->nullable();
            $table->string('sync_status')->default('pending')->nullable();
            $table->integer('sync_attempts')->default(0)->nullable();

            $table->index('employee_id');
            $table->index('employee_number');
            $table->index('event_type');
            $table->index('timestamp');
            $table->index('method');
            $table->index('sync_status');
            $table->index('deleted_at');
            $table->index(['employee_id', 'timestamp']);
            $table->index(['employee_number', 'timestamp']);
            $table->index(['event_type', 'timestamp']);
            $table->index(['sync_status', 'sync_attempts']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clock_events');
    }
};
