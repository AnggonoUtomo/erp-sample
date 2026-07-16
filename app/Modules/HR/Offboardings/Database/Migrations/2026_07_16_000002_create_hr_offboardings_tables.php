<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_offboardings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('hr_employees')->restrictOnDelete();
            $table->foreignId('employee_contract_id')->nullable()->constrained('hr_employee_contracts')->restrictOnDelete();
            $table->foreignId('offboarding_template_id')->constrained('hr_offboarding_templates')->restrictOnDelete();
            $table->foreignId('target_employment_status_id')->constrained('hr_employment_statuses')->restrictOnDelete();
            $table->foreignId('owner_user_id')->constrained('users')->restrictOnDelete();
            $table->date('exit_date');
            $table->string('exit_type', 32);
            $table->text('exit_reason');
            $table->text('notes')->nullable();
            $table->string('status', 24)->default('DRAFT');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['employee_id', 'status', 'exit_date']);
        });

        Schema::create('hr_offboarding_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offboarding_id')->constrained('hr_offboardings')->restrictOnDelete();
            $table->foreignId('source_template_item_id')->nullable()->constrained('hr_offboarding_template_items')->nullOnDelete();
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
            $table->unique(['offboarding_id', 'sort_order'], 'hr_offboarding_tasks_order_unique');
            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_offboarding_tasks');
        Schema::dropIfExists('hr_offboardings');
    }
};
