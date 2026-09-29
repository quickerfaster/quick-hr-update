<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_policies', function (Blueprint $table) {
            $table->integer('tax_year')->nullable()->after('is_statutory');
            $table->index('tax_year');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_policies', function (Blueprint $table) {
            $table->dropIndex(['tax_year']);
            $table->dropColumn('tax_year');
        });
    }
};
