<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_onboarding_tasks', function (Blueprint $table) {
            $table->foreignId('assignee_user_id')->nullable()->after('default_assignee_role')->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by_user_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable()->after('completed_by_user_id');
            $table->text('completion_note')->nullable()->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('hr_onboarding_tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assignee_user_id');
            $table->dropConstrainedForeignId('completed_by_user_id');
            $table->dropColumn(['completed_at', 'completion_note']);
        });
    }
};
