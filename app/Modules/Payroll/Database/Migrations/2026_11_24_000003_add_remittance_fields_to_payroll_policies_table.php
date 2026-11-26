<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_policies', function (Blueprint $table) {
            $table->unsignedInteger('remittance_due_days')->nullable()->after('tax_year')
                ->comment('Days after period end when statutory remittance is due (e.g. 10 for PAYE)');
            $table->decimal('late_penalty_rate', 5, 2)->nullable()->after('remittance_due_days')
                ->comment('Penalty percentage for late remittance (e.g. 10.00 for 10%)');
            $table->string('late_penalty_description')->nullable()->after('late_penalty_rate')
                ->comment('Human-readable description of late-payment consequences');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_policies', function (Blueprint $table) {
            $table->dropColumn([
                'remittance_due_days',
                'late_penalty_rate',
                'late_penalty_description',
            ]);
        });
    }
};
