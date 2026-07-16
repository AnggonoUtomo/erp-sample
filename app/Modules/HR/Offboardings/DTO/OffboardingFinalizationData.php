<?php

namespace App\Modules\HR\Offboardings\DTO;

final readonly class OffboardingFinalizationData
{
    public function __construct(
        public string $businessDate,
        public int $actorUserId,
    ) {}
}
