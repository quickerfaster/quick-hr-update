<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_payslips', function (Blueprint $table) {
            // Year-to-date cumulative totals (tax year: Jan 1 – Dec 31)
            $table->decimal('gross_pay_ytd', 12, 2)->default(0)->nullable()->after('gross_pay')
                ->comment('Cumulative gross pay for the tax year to date');
            $table->decimal('income_tax_ytd', 12, 2)->default(0)->nullable()->after('income_tax')
                ->comment('Cumulative PAYE for the tax year to date');
            $table->decimal('pension_employee_ytd', 12, 2)->default(0)->nullable()->after('pension_employee')
                ->comment('Cumulative employee pension for the tax year to date');
            $table->decimal('pension_employer_ytd', 12, 2)->default(0)->nullable()->after('pension_employer')
                ->comment('Cumulative employer pension for the tax year to date');
            $table->decimal('nhf_ytd', 12, 2)->default(0)->nullable()->after('pension_employer_ytd')
                ->comment('Cumulative NHF contribution for the tax year to date');
            $table->decimal('nhis_employee_ytd', 12, 2)->default(0)->nullable()->after('health_insurance_employee')
                ->comment('Cumulative employee NHIS for the tax year to date');
            $table->decimal('nhis_employer_ytd', 12, 2)->default(0)->nullable()->after('health_insurance_employer')
                ->comment('Cumulative employer NHIS for the tax year to date');
            $table->decimal('net_pay_ytd', 12, 2)->default(0)->nullable()->after('net_pay')
                ->comment('Cumulative net pay for the tax year to date');

            // Statutory identifier snapshot (copied from employee_payroll_profiles at calculation time)
            $table->string('tax_identification_number', 50)->nullable()->after('net_pay_ytd')
                ->comment('TIN snapshot at time of payslip generation');
            $table->string('pension_rsa_number', 50)->nullable()->after('tax_identification_number')
                ->comment('RSA PIN snapshot at time of payslip generation');
            $table->string('nhf_number', 50)->nullable()->after('pension_rsa_number')
                ->comment('NHF number snapshot at time of payslip generation');
            $table->string('nhis_number', 50)->nullable()->after('nhf_number')
                ->comment('NHIS number snapshot at time of payslip generation');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_payslips', function (Blueprint $table) {
            $table->dropColumn([
                'gross_pay_ytd',
                'income_tax_ytd',
                'pension_employee_ytd',
                'pension_employer_ytd',
                'nhf_ytd',
                'nhis_employee_ytd',
                'nhis_employer_ytd',
                'net_pay_ytd',
                'tax_identification_number',
                'pension_rsa_number',
                'nhf_number',
                'nhis_number',
            ]);
        });
    }
};
