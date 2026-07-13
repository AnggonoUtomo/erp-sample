<?php

namespace App\Modules\HR\EmployeeContracts\DTO;

final readonly class EmployeeContractData
{
    public function __construct(
        public int $employeeId, public int $employmentTypeId, public string $contractNumber,
        public string $startDate, public ?string $endDate, public ?string $probationEndDate,
        public ?string $signedDate, public ?string $notes,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            (int) $data['employee_id'], (int) $data['employment_type_id'], strtoupper(trim($data['contract_number'])),
            $data['start_date'], $data['end_date'] ?? null, $data['probation_end_date'] ?? null,
            $data['signed_date'] ?? null, trim((string) ($data['notes'] ?? '')) ?: null,
        );
    }
}
