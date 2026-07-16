<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_employee_movements', function (Blueprint $table): void {
            $table->foreignId('approved_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->foreignId('archived_by')->nullable()->after('cancel_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('archived_at')->nullable()->after('archived_by');
        });
    }

    public function down(): void
    {
        Schema::table('hr_employee_movements', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn('approved_at');
            $table->dropConstrainedForeignId('archived_by');
            $table->dropColumn('archived_at');
        });
    }
};
