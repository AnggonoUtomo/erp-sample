<?php

namespace App\Modules\HR\Onboardings\Enums;

enum OnboardingStatus: string
{
    case Draft = 'DRAFT';
    case InProgress = 'IN_PROGRESS';
    case Completed = 'COMPLETED';
    case Cancelled = 'CANCELLED';

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled], true);
    }

    /** @return list<self> */
    private function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::InProgress, self::Cancelled],
            self::InProgress => [self::Completed, self::Cancelled],
            self::Completed, self::Cancelled => [],
        };
    }
}
