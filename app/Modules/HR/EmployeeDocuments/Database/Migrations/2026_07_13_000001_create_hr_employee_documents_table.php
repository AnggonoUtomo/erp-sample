<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_employee_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_id')->constrained('hr_employees')->restrictOnDelete();
            $table->foreignId('document_type_id')->constrained('hr_reference_data')->restrictOnDelete();
            $table->text('document_number')->nullable();
            $table->char('document_number_fingerprint', 64)->nullable()->index();
            $table->char('document_number_uniqueness_key', 64)->nullable()->unique();
            $table->string('issuer')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('verification_status', 20)->default('PENDING');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->string('verification_reason', 1000)->nullable();
            $table->string('document_reference')->nullable();
            $table->unsignedSmallInteger('document_reference_version')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['employee_id', 'verification_status']);
            $table->index(['document_type_id', 'verification_status']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_employee_documents');
    }
};
