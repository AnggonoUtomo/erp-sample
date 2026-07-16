<?php

namespace App\Modules\HR\Employees\Integration\DTO;

final readonly class EmployeeTerminationResultV1
{
    public const SCHEMA_VERSION = 1;

    public function __construct(
        public int $employeeId,
        public ?int $previousEmploymentStatusId,
        public int $employmentStatusId,
        public string $effectiveDate,
        public string $state = 'TERMINATED',
    ) {}

    /** @return array{schemaVersion: int, employeeId: int, previousEmploymentStatusId: ?int, employmentStatusId: int, effectiveDate: string, state: string} */
    public function toArray(): array
    {
        return [
            'schemaVersion' => self::SCHEMA_VERSION,
            'employeeId' => $this->employeeId,
            'previousEmploymentStatusId' => $this->previousEmploymentStatusId,
            'employmentStatusId' => $this->employmentStatusId,
            'effectiveDate' => $this->effectiveDate,
            'state' => $this->state,
        ];
    }
}
