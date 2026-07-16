<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_offboardings', function (Blueprint $table) {
            $table->string('active_identity_key', 191)->nullable()->after('status');
            $table->char('request_fingerprint', 64)->nullable()->after('active_identity_key');
        });

        DB::table('hr_offboardings')
            ->whereIn('status', ['DRAFT', 'IN_PROGRESS', 'READY_FOR_EXIT'])
            ->orderBy('id')
            ->each(function ($row): void {
                $fingerprint = hash('sha256', json_encode([
                    'employee_id' => (int) $row->employee_id,
                    'employee_contract_id' => $row->employee_contract_id !== null ? (int) $row->employee_contract_id : null,
                    'offboarding_template_id' => (int) $row->offboarding_template_id,
                    'target_employment_status_id' => (int) $row->target_employment_status_id,
                    'owner_user_id' => (int) $row->owner_user_id,
                    'exit_date' => (string) $row->exit_date,
                    'exit_type' => (string) $row->exit_type,
                    'exit_reason' => (string) $row->exit_reason,
                    'notes' => $row->notes !== null ? (string) $row->notes : null,
                ], JSON_THROW_ON_ERROR));

                DB::table('hr_offboardings')
                    ->where('id', $row->id)
                    ->update([
                        'active_identity_key' => "employee:{$row->employee_id}",
                        'request_fingerprint' => $fingerprint,
                    ]);
            });

        Schema::table(
            'hr_offboardings',
            fn (Blueprint $table) => $table->unique(
                'active_identity_key',
                'hr_offboardings_active_identity_unique',
            ),
        );
    }

    public function down(): void
    {
        Schema::table('hr_offboardings', function (Blueprint $table) {
            $table->dropUnique('hr_offboardings_active_identity_unique');
            $table->dropColumn(['active_identity_key', 'request_fingerprint']);
        });
    }
};
