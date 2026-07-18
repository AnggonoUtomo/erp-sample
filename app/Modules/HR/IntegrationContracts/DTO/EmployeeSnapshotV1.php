<?php

namespace App\Modules\HR\IntegrationContracts\DTO;

final readonly class EmployeeSnapshotV1
{
    public const SCHEMA_VERSION = 1;

    public function __construct(
        public int $employeeId,
        public string $employeeNumber,
        public string $displayName,
        public ?string $workEmail,
        public bool $isActive,
        public ?int $linkedUserId,
    ) {}

    /** @return array{schemaVersion: int, employeeId: int, employeeNumber: string, displayName: string, workEmail: ?string, isActive: bool, linkedUserId: ?int} */
    public function toArray(): array
    {
        return [
            'schemaVersion' => self::SCHEMA_VERSION,
            'employeeId' => $this->employeeId,
            'employeeNumber' => $this->employeeNumber,
            'displayName' => $this->displayName,
            'workEmail' => $this->workEmail,
            'isActive' => $this->isActive,
            'linkedUserId' => $this->linkedUserId,
        ];
    }
}
