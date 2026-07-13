<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_employee_documents', function (Blueprint $table): void {
            $table->char('attachment_idempotency_key_hash', 64)->nullable()->after('document_reference_version');
        });
    }

    public function down(): void
    {
        Schema::table('hr_employee_documents', function (Blueprint $table): void {
            $table->dropColumn('attachment_idempotency_key_hash');
        });
    }
};
