<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_payroll_profiles', function (Blueprint $table) {
            $table->string('tax_identification_number', 50)->nullable()->after('extra_withholding')
                ->comment('Nigerian Tax Identification Number (TIN) for PAYE remittance');
            $table->string('pension_rsa_number', 50)->nullable()->after('tax_identification_number')
                ->comment('Retirement Savings Account (RSA) PIN with PenCom-registered PFA');
            $table->string('nhf_number', 50)->nullable()->after('pension_rsa_number')
                ->comment('National Housing Fund registration number (FMBN)');
            $table->string('nhis_number', 50)->nullable()->after('nhf_number')
                ->comment('National Health Insurance Scheme / NHIA enrollee number');
        });
    }

    public function down(): void
    {
        Schema::table('employee_payroll_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'tax_identification_number',
                'pension_rsa_number',
                'nhf_number',
                'nhis_number',
            ]);
        });
    }
};
