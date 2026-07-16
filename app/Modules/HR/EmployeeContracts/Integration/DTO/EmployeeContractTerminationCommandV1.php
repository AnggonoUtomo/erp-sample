<?php

namespace App\Modules\HR\EmployeeContracts\Integration\DTO;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

final readonly class EmployeeContractTerminationCommandV1
{
    public const SCHEMA_VERSION = 1;

    public function __construct(
        public int $contractId,
        public int $employeeId,
        public string $expectedState,
        public string $effectiveDate,
        public string $reason,
        public int $actorUserId,
    ) {
        if ($contractId < 1 || $employeeId < 1 || $actorUserId < 1) {
            throw new InvalidArgumentException('Identifiers must be positive integers.');
        }

        if ($expectedState !== 'ACTIVE') {
            throw new InvalidArgumentException('Expected state must be ACTIVE.');
        }

        if (CarbonImmutable::createFromFormat('!Y-m-d', $effectiveDate)?->format('Y-m-d') !== $effectiveDate) {
            throw new InvalidArgumentException('Effective date must use YYYY-MM-DD format.');
        }

        if (trim($reason) === '' || mb_strlen($reason) > 2000) {
            throw new InvalidArgumentException('Reason must contain between 1 and 2000 characters.');
        }
    }

    /** @return array{schemaVersion: int, contractId: int, employeeId: int, expectedState: string, effectiveDate: string, reason: string, actorUserId: int} */
    public function toArray(): array
    {
        return [
            'schemaVersion' => self::SCHEMA_VERSION,
            'contractId' => $this->contractId,
            'employeeId' => $this->employeeId,
            'expectedState' => $this->expectedState,
            'effectiveDate' => $this->effectiveDate,
            'reason' => $this->reason,
            'actorUserId' => $this->actorUserId,
        ];
    }
}
