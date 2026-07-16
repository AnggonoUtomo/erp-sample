<?php

namespace App\Modules\HR\Offboardings\DTO;

final readonly class OffboardingTaskCompletionData
{
    public function __construct(public int $actorUserId, public ?string $note) {}
}
