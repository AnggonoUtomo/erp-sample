<?php

namespace App\Modules\HR\Onboardings\DTO;

final readonly class OnboardingTaskAssignmentData
{
    public function __construct(public ?int $assigneeUserId) {}
}
