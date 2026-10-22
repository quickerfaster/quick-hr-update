<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('loans', 'current_step')) {
            Schema::table('loans', function (Blueprint $table) {
                $table->integer('current_step')->default(1)->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('loans', 'current_step')) {
            Schema::table('loans', function (Blueprint $table) {
                $table->dropColumn('current_step');
            });
        }
    }
};
