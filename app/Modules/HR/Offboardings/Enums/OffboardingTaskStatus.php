<?php

namespace App\Modules\HR\Offboardings\Enums;

enum OffboardingTaskStatus: string
{
    case Pending = 'PENDING';
    case InProgress = 'IN_PROGRESS';
    case Completed = 'COMPLETED';
    case Skipped = 'SKIPPED';

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Skipped], true);
    }

    /** @return list<self> */
    private function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::InProgress, self::Skipped],
            self::InProgress => [self::Completed, self::Skipped],
            self::Completed, self::Skipped => [self::Pending],
        };
    }
}
