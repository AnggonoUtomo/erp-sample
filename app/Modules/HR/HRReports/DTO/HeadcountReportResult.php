<?php

namespace App\Modules\HR\HRReports\DTO;

final readonly class HeadcountReportResult
{
    /**
     * @param  array<int, array{id: int|null, code: string|null, name: string, employeeCount: int}>  $rows
     */
    public function __construct(
        public string $asOf,
        public string $groupBy,
        public array $rows,
        public int $total,
    ) {}
}
