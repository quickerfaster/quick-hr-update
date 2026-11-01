<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('company_policies')) {
            Schema::create('company_policies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('category')->default('other');
                $table->longText('content')->nullable();
                $table->string('version')->default('1.0');
                $table->string('status')->default('draft');
                $table->date('effective_date')->nullable();
                $table->date('review_date')->nullable();
                $table->boolean('requires_acknowledgment')->default(false);
                $table->date('acknowledgment_required_by')->nullable();
                $table->string('priority')->default('medium');
                $table->json('distribution_scope')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('company_policies');
    }
};
