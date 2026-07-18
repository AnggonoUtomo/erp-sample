<?php

namespace App\Modules\HR\IntegrationContracts\DTO;

final readonly class EmployeeContractSnapshotV1
{
    public const SCHEMA_VERSION = 1;

    public function __construct(
        public int $employeeId,
        public int $contractId,
        public string $contractType,
        public string $status,
        public string $startDate,
        public ?string $endDate,
        public bool $isCurrent,
    ) {}

    /** @return array{schemaVersion: int, employeeId: int, contractId: int, contractType: string, status: string, startDate: string, endDate: ?string, isCurrent: bool} */
    public function toArray(): array
    {
        return [
            'schemaVersion' => self::SCHEMA_VERSION,
            'employeeId' => $this->employeeId,
            'contractId' => $this->contractId,
            'contractType' => $this->contractType,
            'status' => $this->status,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'isCurrent' => $this->isCurrent,
        ];
    }
}
