<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('hr_employees')->nullOnDelete();
            $table->foreignId('departement_id')->nullable()->constrained('hr_departements')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('hr_positions')->nullOnDelete();
            $table->foreignId('job_level_id')->nullable()->constrained('hr_job_levels')->nullOnDelete();
            $table->foreignId('work_location_id')->nullable()->constrained('hr_work_locations')->nullOnDelete();
            $table->foreignId('employment_status_id')->nullable()->constrained('hr_employment_statuses')->nullOnDelete();
            $table->foreignId('employment_type_id')->nullable()->constrained('hr_employment_types')->nullOnDelete();
            $table->string('employee_number', 64)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('display_name', 160);
            $table->string('work_email')->nullable()->unique();
            $table->string('personal_email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth', 120)->nullable();
            $table->string('national_id', 64)->nullable()->unique();
            $table->text('address')->nullable();
            $table->string('emergency_contact_name', 160)->nullable();
            $table->string('emergency_contact_phone', 40)->nullable();
            $table->string('emergency_contact_relation', 80)->nullable();
            $table->date('hired_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['active', 'deleted_at']);
            $table->index(['departement_id', 'position_id']);
            $table->index('supervisor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_employees');
    }
};
