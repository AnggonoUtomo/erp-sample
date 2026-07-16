<?php

namespace App\Modules\HR\Offboardings\DTO;

final readonly class OffboardingTaskAssignmentData
{
    public function __construct(public ?int $assigneeUserId) {}
}
