<?php

namespace App\Modules\HR\EmployeeContracts\Integration\DTO;

final readonly class EmployeeContractTerminationResultV1
{
    public const SCHEMA_VERSION = 1;

    public function __construct(
        public int $contractId,
        public int $employeeId,
        public string $effectiveDate,
        public string $state = 'ENDED',
    ) {}

    /** @return array{schemaVersion: int, contractId: int, employeeId: int, effectiveDate: string, state: string} */
    public function toArray(): array
    {
        return [
            'schemaVersion' => self::SCHEMA_VERSION,
            'contractId' => $this->contractId,
            'employeeId' => $this->employeeId,
            'effectiveDate' => $this->effectiveDate,
            'state' => $this->state,
        ];
    }
}
