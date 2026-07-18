<?php

namespace App\Modules\HR\HRReports\DTO;

use Carbon\CarbonImmutable;

final readonly class HeadcountReportFilters
{
    /**
     * @param  array<int, int>  $employmentStatusIds
     */
    public function __construct(
        public string $asOf,
        public array $employmentStatusIds = [],
    ) {}

    public function asOfDate(): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('Y-m-d', $this->asOf)->startOfDay();
    }
}
