<?php

namespace App\Modules\HR\EmployeeContracts\Integration\DTO;

use Carbon\CarbonImmutable;

final readonly class EmployeeContractSnapshotV1
{
    public const SCHEMA_VERSION = 1;

    public function __construct(
        public int $employeeId,
        public int $contractId,
        public string $employmentTypeCode,
        public string $validFrom,
        public ?string $validUntil,
        public CarbonImmutable $capturedAt,
    ) {}

    /** @return array{schemaVersion: int, employeeId: int, contractId: int, employmentTypeCode: string, validFrom: string, validUntil: ?string, capturedAt: string} */
    public function toArray(): array
    {
        return [
            'schemaVersion' => self::SCHEMA_VERSION,
            'employeeId' => $this->employeeId,
            'contractId' => $this->contractId,
            'employmentTypeCode' => $this->employmentTypeCode,
            'validFrom' => $this->validFrom,
            'validUntil' => $this->validUntil,
            'capturedAt' => $this->capturedAt->utc()->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}
