<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employee_number_sequence', function (Blueprint $table) {
            $table->string('name')->primary();
            $table->bigInteger('current_value')->default(0);
            $table->timestamps();
        });

        // Seed with the next value after the current max trailing number.
        // Extracts the numeric suffix from all existing employee numbers
        // (e.g. EMP5000 → 5000, EMPLOYEE-2026-00012 → 12, EMP-MC-1-025 → 25)
        // and starts the sequence at max + 1.
        $maxSeq = 0;
        $numbers = DB::table('employees')->pluck('employee_number');

        foreach ($numbers as $number) {
            if (preg_match('/(\d+)$/', $number, $matches)) {
                $maxSeq = max($maxSeq, (int) $matches[1]);
            }
        }

        DB::table('employee_number_sequence')->insert([
            'name'          => 'employee_number',
            'current_value' => $maxSeq + 1,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('employee_number_sequence');
    }
};
