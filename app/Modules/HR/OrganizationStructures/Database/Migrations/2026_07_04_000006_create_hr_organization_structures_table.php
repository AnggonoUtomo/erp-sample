<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_organization_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('hr_organization_structures')->nullOnDelete();
            $table->foreignId('departement_id')->nullable()->constrained('hr_departements')->restrictOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('hr_positions')->restrictOnDelete();
            $table->string('code', 64)->unique();
            $table->string('name');
            $table->string('node_type', 32)->default('unit');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['parent_id', 'active', 'sort_order'], 'hr_org_structures_parent_active_sort_index');
            $table->index(['departement_id', 'position_id'], 'hr_org_structures_dept_position_index');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_organization_structures');
    }
};
