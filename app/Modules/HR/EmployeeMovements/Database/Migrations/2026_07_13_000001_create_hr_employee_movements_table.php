<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_employee_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_id')->constrained('hr_employees')->restrictOnDelete();
            $table->string('type', 30)->default('TRANSFER');
            $table->date('effective_date');
            $table->string('status', 20)->default('DRAFT');
            $table->string('reason', 500);
            $table->text('notes')->nullable();
            $table->json('before_values');
            $table->json('after_values');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('applied_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['employee_id', 'effective_date']);
            $table->index(['status', 'effective_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_employee_movements');
    }
};
