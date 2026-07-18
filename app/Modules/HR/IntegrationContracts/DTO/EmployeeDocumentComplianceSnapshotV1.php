<?php

namespace App\Modules\HR\IntegrationContracts\DTO;

final readonly class EmployeeDocumentComplianceSnapshotV1
{
    public const SCHEMA_VERSION = 1;

    public function __construct(
        public int $employeeId,
        public int $requiredCount,
        public int $verifiedCount,
        public int $pendingCount,
        public int $expiredCount,
        public int $expiringCount,
        public string $asOf,
    ) {}

    /** @return array{schemaVersion: int, employeeId: int, requiredCount: int, verifiedCount: int, pendingCount: int, expiredCount: int, expiringCount: int, asOf: string} */
    public function toArray(): array
    {
        return [
            'schemaVersion' => self::SCHEMA_VERSION,
            'employeeId' => $this->employeeId,
            'requiredCount' => $this->requiredCount,
            'verifiedCount' => $this->verifiedCount,
            'pendingCount' => $this->pendingCount,
            'expiredCount' => $this->expiredCount,
            'expiringCount' => $this->expiringCount,
            'asOf' => $this->asOf,
        ];
    }
}
