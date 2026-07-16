<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_offboarding_tasks', function (Blueprint $table) {
            $table->foreignId('skipped_by_user_id')->nullable()->after('completion_note')->constrained('users')->nullOnDelete();
            $table->timestamp('skipped_at')->nullable()->after('skipped_by_user_id');
            $table->text('skip_reason')->nullable()->after('skipped_at');
            $table->foreignId('reopened_by_user_id')->nullable()->after('skip_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('reopened_at')->nullable()->after('reopened_by_user_id');
            $table->text('reopen_reason')->nullable()->after('reopened_at');
        });
    }

    public function down(): void
    {
        Schema::table('hr_offboarding_tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('skipped_by_user_id');
            $table->dropConstrainedForeignId('reopened_by_user_id');
            $table->dropColumn(['skipped_at', 'skip_reason', 'reopened_at', 'reopen_reason']);
        });
    }
};
