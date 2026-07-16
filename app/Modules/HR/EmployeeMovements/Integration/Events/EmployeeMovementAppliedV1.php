<?php

namespace App\Modules\HR\EmployeeMovements\Integration\Events;

use App\Modules\HR\EmployeeMovements\Models\EmployeeMovement;

final readonly class EmployeeMovementAppliedV1
{
    public function __construct(
        public int $movementId,
        public int $employeeId,
        public string $movementType,
        public string $effectiveDate,
        public array $changedFields,
        public ?int $approvedBy,
        public ?int $appliedBy,
    ) {}

    public static function fromMovement(EmployeeMovement $movement): self
    {
        $before = $movement->before_values ?? [];
        $after = $movement->after_values ?? [];
        $changedFields = collect($after)
            ->filter(fn ($value, string $field): bool => ($before[$field] ?? null) !== $value)
            ->keys()
            ->values()
            ->all();

        return new self(
            movementId: $movement->id,
            employeeId: $movement->employee_id,
            movementType: $movement->type,
            effectiveDate: $movement->effective_date->toDateString(),
            changedFields: $changedFields,
            approvedBy: $movement->approved_by,
            appliedBy: $movement->applied_by,
        );
    }

    /**
     * @return array{schemaVersion: int, movementId: int, employeeId: int, movementType: string, effectiveDate: string, changedFields: array<int, string>, approvedBy: ?int, appliedBy: ?int}
     */
    public function toArray(): array
    {
        return [
            'schemaVersion' => 1,
            'movementId' => $this->movementId,
            'employeeId' => $this->employeeId,
            'movementType' => $this->movementType,
            'effectiveDate' => $this->effectiveDate,
            'changedFields' => $this->changedFields,
            'approvedBy' => $this->approvedBy,
            'appliedBy' => $this->appliedBy,
        ];
    }
}
