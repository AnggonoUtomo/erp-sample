<?php

namespace App\Modules\HR\Offboardings\DTO;

final readonly class OffboardingTaskReasonData
{
    public function __construct(public int $actorUserId, public string $reason) {}
}
