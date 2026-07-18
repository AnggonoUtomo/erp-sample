<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_employment_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('requires_contract_end_date')->default(false);
            $table->boolean('included_in_payroll')->default(true);
            $table->boolean('eligible_for_benefits')->default(true);
            $table->boolean('eligible_for_overtime')->default(true);
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['active', 'requires_contract_end_date', 'sort_order'], 'hr_employment_types_active_contract_sort_index');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_employment_types');
    }
};
