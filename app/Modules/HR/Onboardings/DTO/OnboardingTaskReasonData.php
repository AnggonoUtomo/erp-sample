<?php

namespace App\Modules\HR\Onboardings\DTO;

final readonly class OnboardingTaskReasonData
{
    public function __construct(public int $actorUserId, public string $reason) {}
}
