<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('onboarding_status')->nullable()->after('company_id')
                ->comment('complete, position_pending, company_pending');
            $table->index('onboarding_status');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['onboarding_status']);
            $table->dropColumn('onboarding_status');
        });
    }
};
