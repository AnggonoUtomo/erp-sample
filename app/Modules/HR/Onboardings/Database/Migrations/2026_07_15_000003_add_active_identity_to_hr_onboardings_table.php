<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_onboardings', function (Blueprint $table) {
            $table->string('active_identity_key', 191)->nullable()->after('status');
            $table->char('request_fingerprint', 64)->nullable()->after('active_identity_key');
        });

        DB::table('hr_onboardings')->whereIn('status', ['DRAFT', 'IN_PROGRESS'])->orderBy('id')->each(function ($row) {
            $identity = $row->employee_contract_id ? "contract:{$row->employee_contract_id}" : "employee:{$row->employee_id}:start:{$row->start_date}";
            $fingerprint = hash('sha256', json_encode([
                'employee_id' => $row->employee_id,
                'employee_contract_id' => $row->employee_contract_id,
                'onboarding_template_id' => $row->onboarding_template_id,
                'owner_user_id' => $row->owner_user_id,
                'start_date' => $row->start_date,
            ], JSON_THROW_ON_ERROR));
            DB::table('hr_onboardings')->where('id', $row->id)->update(['active_identity_key' => $identity, 'request_fingerprint' => $fingerprint]);
        });

        Schema::table('hr_onboardings', fn (Blueprint $table) => $table->unique('active_identity_key', 'hr_onboardings_active_identity_unique'));
    }

    public function down(): void
    {
        Schema::table('hr_onboardings', function (Blueprint $table) {
            $table->dropUnique('hr_onboardings_active_identity_unique');
            $table->dropColumn(['active_identity_key', 'request_fingerprint']);
        });
    }
};
