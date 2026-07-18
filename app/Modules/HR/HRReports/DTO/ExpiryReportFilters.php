<?php

namespace App\Modules\HR\HRReports\DTO;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

final readonly class ExpiryReportFilters
{
    public function __construct(
        public string $asOf,
        public int $withinDays,
    ) {
        if ($withinDays < 0 || $withinDays > 3650) {
            throw new InvalidArgumentException('Expiry report window must be between 0 and 3650 days.');
        }
    }

    public function asOfDate(): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('!Y-m-d', $this->asOf);
    }
}
