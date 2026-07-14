<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_onboardings', function (Blueprint $table) {
            $table->foreignId('completed_by_user_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable()->after('completed_by_user_id');
            $table->foreignId('cancelled_by_user_id')->nullable()->after('completed_at')->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by_user_id');
            $table->text('cancel_reason')->nullable()->after('cancelled_at');
        });
    }

    public function down(): void
    {
        Schema::table('hr_onboardings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('completed_by_user_id');
            $table->dropConstrainedForeignId('cancelled_by_user_id');
            $table->dropColumn(['completed_at', 'cancelled_at', 'cancel_reason']);
        });
    }
};
