<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_payslips', function (Blueprint $table) {
            $table->unsignedInteger('worked_days')->nullable()->after('net_pay')
                ->comment('Number of approved attendance days counted for salaried_daily employees');
            $table->unsignedInteger('total_workdays')->nullable()->after('worked_days')
                ->comment('Total workdays in the payroll period per the resolved work pattern');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_payslips', function (Blueprint $table) {
            $table->dropColumn(['worked_days', 'total_workdays']);
        });
    }
};
