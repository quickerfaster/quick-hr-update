<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'subdomain')) {
                $table->string('subdomain', 100)->nullable()->unique();
            }
            if (!Schema::hasColumn('companies', 'logo')) {
                $table->string('logo', 255)->nullable();
            }
            if (!Schema::hasColumn('companies', 'email')) {
                $table->string('email', 255)->nullable();
            }
            if (!Schema::hasColumn('companies', 'phone')) {
                $table->string('phone', 50)->nullable();
            }
            if (!Schema::hasColumn('companies', 'website')) {
                $table->string('website', 255)->nullable();
            }
            if (!Schema::hasColumn('companies', 'address')) {
                $table->text('address')->nullable();
            }
            if (!Schema::hasColumn('companies', 'city')) {
                $table->string('city', 100)->nullable();
            }
            if (!Schema::hasColumn('companies', 'state_code')) {
                $table->string('state_code', 100)->nullable();
            }
            if (!Schema::hasColumn('companies', 'country_code')) {
                $table->string('country_code', 100)->nullable();
            }
            if (!Schema::hasColumn('companies', 'postal_code')) {
                $table->string('postal_code', 20)->nullable();
            }
            if (!Schema::hasColumn('companies', 'tax_id')) {
                $table->string('tax_id', 100)->nullable();
            }
            if (!Schema::hasColumn('companies', 'registration_number')) {
                $table->string('registration_number', 100)->nullable();
            }
            if (!Schema::hasColumn('companies', 'currency_code')) {
                $table->string('currency_code', 3)->nullable()->default('USD');
            }
            if (!Schema::hasColumn('companies', 'timezone')) {
                $table->string('timezone', 50)->nullable()->default('UTC');
            }
            if (!Schema::hasColumn('companies', 'date_format')) {
                $table->string('date_format', 20)->nullable()->default('Y-m-d');
            }
            if (!Schema::hasColumn('companies', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (!Schema::hasColumn('companies', 'status')) {
                $table->string('status', 50)->default('active');
            }
            if (!Schema::hasColumn('companies', 'metadata')) {
                $table->json('metadata')->nullable();
            }
            if (!Schema::hasColumn('companies', 'level')) {
                $table->string('level')->default('division')->nullable();
            }
            if (!Schema::hasColumn('companies', 'parent_company_id')) {
                $table->foreignId('parent_company_id')->nullable()->constrained('companies')->nullOnDelete();
            }
            if (!Schema::hasColumn('companies', 'billing_email')) {
                $table->string('billing_email')->nullable();
            }
            if (!Schema::hasColumn('companies', 'billing_address_line_1')) {
                $table->string('billing_address_line_1')->nullable();
            }
            if (!Schema::hasColumn('companies', 'billing_address_line_2')) {
                $table->string('billing_address_line_2')->nullable();
            }
            if (!Schema::hasColumn('companies', 'billing_city')) {
                $table->string('billing_city')->nullable();
            }
            if (!Schema::hasColumn('companies', 'billing_state_code')) {
                $table->string('billing_state_code')->nullable();
            }
            if (!Schema::hasColumn('companies', 'billing_postal_code')) {
                $table->string('billing_postal_code')->nullable();
            }
            if (!Schema::hasColumn('companies', 'billing_country_code')) {
                $table->string('billing_country_code')->nullable();
            }
            if (!Schema::hasColumn('companies', 'database_name')) {
                $table->string('database_name')->nullable();
            }
            if (!Schema::hasColumn('companies', 'is_placeholder')) {
                $table->boolean('is_placeholder')->default(true);
            }
            if (!Schema::hasColumn('companies', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'parent_company_id')) {
                $table->dropForeign(['parent_company_id']);
            }

            $columns = [
                'deleted_at',
                'is_placeholder',
                'database_name',
                'billing_country_code',
                'billing_postal_code',
                'billing_state_code',
                'billing_city',
                'billing_address_line_2',
                'billing_address_line_1',
                'billing_email',
                'parent_company_id',
                'level',
                'metadata',
                'status',
                'is_active',
                'date_format',
                'timezone',
                'currency_code',
                'registration_number',
                'tax_id',
                'postal_code',
                'country_code',
                'state_code',
                'city',
                'address',
                'website',
                'phone',
                'email',
                'logo',
                'subdomain',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('companies', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
