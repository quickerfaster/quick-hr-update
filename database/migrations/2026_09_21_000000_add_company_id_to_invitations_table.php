<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('invitations') && !Schema::hasColumn('invitations', 'company_id')) {
            Schema::table('invitations', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('role')->constrained('companies')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('invitations') && Schema::hasColumn('invitations', 'company_id')) {
            Schema::table('invitations', function (Blueprint $table) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            });
        }
    }
};
