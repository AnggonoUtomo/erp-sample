<?php

namespace App\Modules\HR\IntegrationContracts\DTO;

final readonly class EmployeeAssignmentSnapshotV1
{
    public const SCHEMA_VERSION = 1;

    /** @var array{id: int|string|null, code: ?string, name: ?string}|null */
    public ?array $departement;

    /** @var array{id: int|string|null, code: ?string, name: ?string}|null */
    public ?array $position;

    /** @var array{id: int|string|null, code: ?string, name: ?string}|null */
    public ?array $jobLevel;

    /** @var array{id: int|string|null, code: ?string, name: ?string}|null */
    public ?array $workLocation;

    /** @var array{id: int|string|null, code: ?string, name: ?string, requiresAttendance: bool, includedInPayroll: bool, isTerminal: bool}|null */
    public ?array $employmentStatus;

    /** @var array{id: int|string|null, code: ?string, name: ?string, requiresContractEndDate: bool, includedInPayroll: bool, eligibleForOvertime: bool}|null */
    public ?array $employmentType;

    public function __construct(
        public int $employeeId,
        public string $effectiveDate,
        ?array $departement,
        ?array $position,
        ?array $jobLevel,
        ?array $workLocation,
        ?array $employmentStatus,
        ?array $employmentType,
    ) {
        $this->departement = self::basicReference($departement);
        $this->position = self::basicReference($position);
        $this->jobLevel = self::basicReference($jobLevel);
        $this->workLocation = self::basicReference($workLocation);
        $this->employmentStatus = self::statusReference($employmentStatus);
        $this->employmentType = self::typeReference($employmentType);
    }

    /**
     * @return array{
     *     schemaVersion: int,
     *     employeeId: int,
     *     effectiveDate: string,
     *     departement: array{id: int|string|null, code: ?string, name: ?string}|null,
     *     position: array{id: int|string|null, code: ?string, name: ?string}|null,
     *     jobLevel: array{id: int|string|null, code: ?string, name: ?string}|null,
     *     workLocation: array{id: int|string|null, code: ?string, name: ?string}|null,
     *     employmentStatus: array{id: int|string|null, code: ?string, name: ?string, requiresAttendance: bool, includedInPayroll: bool, isTerminal: bool}|null,
     *     employmentType: array{id: int|string|null, code: ?string, name: ?string, requiresContractEndDate: bool, includedInPayroll: bool, eligibleForOvertime: bool}|null
     * }
     */
    public function toArray(): array
    {
        return [
            'schemaVersion' => self::SCHEMA_VERSION,
            'employeeId' => $this->employeeId,
            'effectiveDate' => $this->effectiveDate,
            'departement' => $this->departement,
            'position' => $this->position,
            'jobLevel' => $this->jobLevel,
            'workLocation' => $this->workLocation,
            'employmentStatus' => $this->employmentStatus,
            'employmentType' => $this->employmentType,
        ];
    }

    /** @return array{id: int|string|null, code: ?string, name: ?string}|null */
    private static function basicReference(?array $reference): ?array
    {
        if ($reference === null) {
            return null;
        }

        return [
            'id' => $reference['id'] ?? null,
            'code' => $reference['code'] ?? null,
            'name' => $reference['name'] ?? null,
        ];
    }

    /** @return array{id: int|string|null, code: ?string, name: ?string, requiresAttendance: bool, includedInPayroll: bool, isTerminal: bool}|null */
    private static function statusReference(?array $reference): ?array
    {
        $basic = self::basicReference($reference);

        if ($basic === null) {
            return null;
        }

        return [
            ...$basic,
            'requiresAttendance' => (bool) ($reference['requiresAttendance'] ?? false),
            'includedInPayroll' => (bool) ($reference['includedInPayroll'] ?? false),
            'isTerminal' => (bool) ($reference['isTerminal'] ?? false),
        ];
    }

    /** @return array{id: int|string|null, code: ?string, name: ?string, requiresContractEndDate: bool, includedInPayroll: bool, eligibleForOvertime: bool}|null */
    private static function typeReference(?array $reference): ?array
    {
        $basic = self::basicReference($reference);

        if ($basic === null) {
            return null;
        }

        return [
            ...$basic,
            'requiresContractEndDate' => (bool) ($reference['requiresContractEndDate'] ?? false),
            'includedInPayroll' => (bool) ($reference['includedInPayroll'] ?? false),
            'eligibleForOvertime' => (bool) ($reference['eligibleForOvertime'] ?? false),
        ];
    }
}
