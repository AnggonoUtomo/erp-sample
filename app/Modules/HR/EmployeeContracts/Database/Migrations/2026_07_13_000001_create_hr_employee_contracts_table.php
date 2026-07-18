<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('hr_employees')->restrictOnDelete();
            $table->foreignId('employment_type_id')->constrained('hr_employment_types')->restrictOnDelete();
            $table->string('contract_number', 100)->unique();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('probation_end_date')->nullable();
            $table->date('signed_date')->nullable();
            $table->string('status', 20)->default('DRAFT');
            $table->string('ended_reason', 500)->nullable();
            $table->foreignId('superseded_by_id')->nullable()->constrained('hr_employee_contracts')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['employee_id', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_employee_contracts');
    }
};
