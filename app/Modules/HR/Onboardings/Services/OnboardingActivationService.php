<?php

namespace App\Modules\HR\Onboardings\Services;

use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\HR\Onboardings\Enums\OnboardingStatus;
use App\Modules\HR\Onboardings\Models\Onboarding;
use App\Modules\HR\Onboardings\Transactions\OnboardingTransaction;
use Illuminate\Validation\ValidationException;

class OnboardingActivationService
{
    public function __construct(private readonly OnboardingTransaction $transaction, private readonly AuditLogService $audit) {}

    public function activate(Onboarding $onboarding): Onboarding
    {
        return $this->transaction->run(function () use ($onboarding) {
            $locked = Onboarding::query()->withTrashed()->lockForUpdate()->findOrFail($onboarding->id);

            if ($locked->status === OnboardingStatus::InProgress && ! $locked->trashed()) {
                return $locked;
            }

            if ($locked->trashed() || $locked->status !== OnboardingStatus::Draft || ! $locked->active_identity_key) {
                throw ValidationException::withMessages([
                    'status' => 'Hanya draft aktif dengan employment identity valid yang dapat diaktifkan.',
                ]);
            }

            $hasConflict = Onboarding::query()
                ->where('active_identity_key', $locked->active_identity_key)
                ->whereKeyNot($locked->id)
                ->whereIn('status', [OnboardingStatus::Draft->value, OnboardingStatus::InProgress->value])
                ->lockForUpdate()
                ->exists();

            if ($hasConflict) {
                throw ValidationException::withMessages([
                    'status' => 'Employment period ini sudah memiliki onboarding aktif lain.',
                ]);
            }

            $locked->update(['status' => OnboardingStatus::InProgress]);
            $this->audit->record(
                module: 'hr.onboardings',
                event: 'Onboarding.activated',
                auditable: $locked,
                description: "Activated onboarding {$locked->id}",
                oldValues: ['status' => OnboardingStatus::Draft->value],
                newValues: ['status' => OnboardingStatus::InProgress->value],
            );

            return $locked->refresh();
        });
    }
}
