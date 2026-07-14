<?php

namespace App\Modules\HR\Onboardings\DTO;

final readonly class OnboardingTaskCompletionData
{
    public function __construct(public int $actorUserId, public ?string $note) {}
}
