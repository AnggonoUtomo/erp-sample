<?php

namespace App\Modules\HR\HRReports\DTO;

use JsonSerializable;

final readonly class ExpiryReportResult implements JsonSerializable
{
    /**
     * @param  array<int, array{
     *     employeeId: int,
     *     employeeNumber: string,
     *     employeeName: string,
     *     typeLabel: string,
     *     expiresAt: string,
     *     daysRemaining: int,
     *     state: 'EXPIRED'|'EXPIRING'
     * }>  $rows
     */
    public function __construct(
        public string $asOf,
        public int $withinDays,
        public array $rows,
        public int $total,
    ) {}

    /**
     * @return array{asOf: string, withinDays: int, rows: array<int, array<string, mixed>>, total: int}
     */
    public function jsonSerialize(): array
    {
        return [
            'asOf' => $this->asOf,
            'withinDays' => $this->withinDays,
            'rows' => $this->rows,
            'total' => $this->total,
        ];
    }
}
