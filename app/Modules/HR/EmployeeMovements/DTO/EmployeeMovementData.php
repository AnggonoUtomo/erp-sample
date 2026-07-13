<?php

namespace App\Modules\HR\EmployeeMovements\DTO;

readonly class EmployeeMovementData
{
    public function __construct(
        public int $employeeId,
        public string $effectiveDate,
        public ?int $departementId,
        public ?int $positionId,
        public ?int $workLocationId,
        public ?int $supervisorId,
        public string $reason,
        public ?string $notes,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            employeeId: (int) $data['employee_id'], effectiveDate: $data['effective_date'],
            departementId: isset($data['departement_id']) ? (int) $data['departement_id'] : null,
            positionId: isset($data['position_id']) ? (int) $data['position_id'] : null,
            workLocationId: isset($data['work_location_id']) ? (int) $data['work_location_id'] : null,
            supervisorId: isset($data['supervisor_id']) ? (int) $data['supervisor_id'] : null,
            reason: trim($data['reason']), notes: trim((string) ($data['notes'] ?? '')) ?: null,
        );
    }
}
