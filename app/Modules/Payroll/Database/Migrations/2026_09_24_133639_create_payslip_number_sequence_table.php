<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payslip_number_sequence', function (Blueprint $table) {
            $table->string('name')->primary();
            $table->bigInteger('current_value')->default(0);
            $table->timestamps();
        });

        // Seed with the next value after the current max trailing number
        // from existing payslip numbers (e.g. PAYSLIP-2026-09-000042 → 42).
        $maxSeq = 0;
        if (Schema::hasTable('payroll_payslips')) {
            $numbers = DB::table('payroll_payslips')->pluck('payslip_number');
            foreach ($numbers as $number) {
                if (preg_match('/(\d+)$/', $number, $matches)) {
                    $maxSeq = max($maxSeq, (int) $matches[1]);
                }
            }
        }

        DB::table('payslip_number_sequence')->insert([
            'name'          => 'payslip_number',
            'current_value' => $maxSeq,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('payslip_number_sequence');
    }
};
