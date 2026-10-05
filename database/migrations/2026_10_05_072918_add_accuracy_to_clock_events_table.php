<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clock_events', function (Blueprint $table) {
            $table->decimal('accuracy', 8, 2)->nullable()->after('longitude')
                ->comment('Browser geolocation accuracy in meters (lower = better)');
        });
    }

    public function down(): void
    {
        Schema::table('clock_events', function (Blueprint $table) {
            $table->dropColumn('accuracy');
        });
    }
};
