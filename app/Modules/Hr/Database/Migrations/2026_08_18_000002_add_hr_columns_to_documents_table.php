<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('documents', 'company_id')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id');
                $table->index('company_id');
            });
        }

        if (!Schema::hasColumn('documents', 'employee_id')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->foreignId('employee_id')->after('company_id')->constrained('employees', 'id')->onDelete('cascade');
                $table->index('employee_id');
            });
        }

        if (!Schema::hasColumn('documents', 'type')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->string('type')->after('employee_id');
                $table->index('type');
            });
        }

        if (!Schema::hasColumn('documents', 'document')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->string('document')->after('type');
            });
        }

        if (!Schema::hasColumn('documents', 'uploaded_at')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->date('uploaded_at')->nullable()->after('document');
                $table->index('uploaded_at');
            });
        }

        if (!Schema::hasColumn('documents', 'expiry_date')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->date('expiry_date')->nullable()->after('uploaded_at');
                $table->index('expiry_date');
            });
        }

        if (!Schema::hasColumn('documents', 'description')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->text('description')->nullable()->after('expiry_date');
            });
        }
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['company_id']);
            $table->dropIndex(['employee_id']);
            $table->dropIndex(['type']);
            $table->dropIndex(['uploaded_at']);
            $table->dropIndex(['expiry_date']);

            $table->dropForeign(['employee_id']);

            $table->dropColumn([
                'company_id',
                'employee_id',
                'type',
                'document',
                'uploaded_at',
                'expiry_date',
                'description',
            ]);
        });
    }
};