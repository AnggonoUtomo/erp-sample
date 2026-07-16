<?php

namespace App\Modules\HR\Offboardings\Integration\Events;

use App\Modules\HR\Offboardings\Models\Offboarding;
use App\Shared\Events\BaseDomainEvent;
use Carbon\CarbonImmutable;

final class EmployeeOffboardingCompletedV1 extends BaseDomainEvent
{
    public static function fromOffboarding(Offboarding $offboarding): self
    {
        $finalizedAt = CarbonImmutable::parse((string) $offboarding->finalized_at);

        return new self(
            aggregateId: (string) $offboarding->id,
            payload: [
                'schema_version' => 1,
                'offboarding_id' => $offboarding->id,
                'employee_id' => $offboarding->employee_id,
                'employee_contract_id' => $offboarding->employee_contract_id,
                'target_employment_status_id' => $offboarding->target_employment_status_id,
                'exit_type' => $offboarding->exit_type->value,
                'effective_date' => $offboarding->exit_date->format('Y-m-d'),
                'business_date' => $offboarding->finalization_business_date?->format('Y-m-d'),
                'finalized_by_user_id' => $offboarding->finalized_by_user_id,
            ],
            metadata: [
                'delivery' => 'sync-after-commit',
                'retry' => 'consumer-idempotent-by-event-id',
                'ordering' => 'per-offboarding-finalized-at',
            ],
            eventId: sprintf(
                'hr-offboarding-completed-v1:%s:%s',
                $offboarding->id,
                $finalizedAt->toIso8601String(),
            ),
            occurredAt: $finalizedAt,
        );
    }
}
