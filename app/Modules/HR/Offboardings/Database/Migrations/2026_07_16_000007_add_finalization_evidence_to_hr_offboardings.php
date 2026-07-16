<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_offboardings', function (Blueprint $table) {
            $table->foreignId('finalized_by_user_id')->nullable()->after('cancel_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('finalized_at')->nullable()->after('finalized_by_user_id');
            $table->date('finalization_business_date')->nullable()->after('finalized_at');
        });
    }

    public function down(): void
    {
        Schema::table('hr_offboardings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('finalized_by_user_id');
            $table->dropColumn(['finalized_at', 'finalization_business_date']);
        });
    }
};
