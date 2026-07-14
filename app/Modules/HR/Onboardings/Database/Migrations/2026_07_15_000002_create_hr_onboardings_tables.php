<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_onboardings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('hr_employees')->restrictOnDelete();
            $table->foreignId('employee_contract_id')->nullable()->constrained('hr_employee_contracts')->restrictOnDelete();
            $table->foreignId('onboarding_template_id')->constrained('hr_onboarding_templates')->restrictOnDelete();
            $table->foreignId('owner_user_id')->constrained('users')->restrictOnDelete();
            $table->date('start_date');
            $table->string('status', 20)->default('DRAFT');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['employee_id', 'status', 'start_date']);
        });

        Schema::create('hr_onboarding_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onboarding_id')->constrained('hr_onboardings')->restrictOnDelete();
            $table->foreignId('source_template_item_id')->nullable()->constrained('hr_onboarding_template_items')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category', 64);
            $table->boolean('required')->default(true);
            $table->smallInteger('due_offset_days')->default(0);
            $table->date('due_date');
            $table->string('default_assignee_role', 100)->nullable();
            $table->unsignedInteger('sort_order');
            $table->string('status', 20)->default('PENDING');
            $table->timestamps();
            $table->unique(['onboarding_id', 'sort_order'], 'hr_onboarding_tasks_order_unique');
            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_onboarding_tasks');
        Schema::dropIfExists('hr_onboardings');
    }
};
